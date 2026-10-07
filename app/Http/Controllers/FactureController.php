<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Style\Style;

class FactureController extends Controller
{
    /**
     * Lecture rapide (streaming) de la première feuille d'un fichier.
     * Retourne le même format que Excel::toArray()[0] : liste de lignes.
     */
    private function readFirstSheet($file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $reader = $ext === 'csv' ? new CsvReader() : new XlsxReader();
        $reader->open($file->getRealPath());

        $rows = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = array_map(
                    fn($v) => $v instanceof \DateTimeInterface ? $v->format('d/m/Y') : $v,
                    $row->toArray()
                );
            }
            break; // première feuille uniquement
        }

        $reader->close();

        return $rows;
    }

    public function processExcel(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        // =========================================================
        // 1. VALIDATION DES 3 FICHIERS
        // =========================================================

        $request->validate([
            'excel_file'  => 'required|file|max:51200',
            'sav_mc_file' => 'nullable|file|max:51200',
            'third_file'  => 'nullable|file|max:51200',
        ]);

        foreach (['excel_file', 'sav_mc_file', 'third_file'] as $field) {
            if (
                $request->hasFile($field) &&
                !in_array(
                    strtolower($request->file($field)->getClientOriginalExtension()),
                    ['xlsx', 'csv'],
                    true
                )
            ) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    $field => 'Le fichier doit être au format .xlsx ou .csv.',
                ]);
            }
        }

        // =========================================================
        // 2. LECTURE DU FICHIER PRINCIPAL
        // =========================================================

        $primaryRows = $this->readFirstSheet(
            $request->file('excel_file')
        );

        if (empty($primaryRows)) {
            return back()->with(
                'error',
                'Le fichier principal est vide.'
            );
        }

        // =========================================================
        // 3. LECTURE DU FICHIER SAV MC
        // =========================================================

        $savRows = $request->hasFile('sav_mc_file')
            ? $this->readFirstSheet($request->file('sav_mc_file'))
            : [];

        $savMap = [];

        if (!empty($savRows)) {

            $savHeader = array_map(
                fn($v) => trim((string) $v),
                $savRows[0]
            );

            $colSavIdRdv = array_search(
                'Étiquettes de lignes',
                $savHeader,
                true
            );

            if ($colSavIdRdv !== false) {

                $colSavValue = $colSavIdRdv + 1;

                foreach (
                    array_slice($savRows, 1) as $savRow
                ) {

                    $id = isset($savRow[$colSavIdRdv])
                        ? trim((string) $savRow[$colSavIdRdv])
                        : '';

                    if ($id === '') {
                        continue;
                    }

                    if (
                        !array_key_exists(
                            $colSavValue,
                            $savRow
                        ) ||
                        $savRow[$colSavValue] === null ||
                        trim((string) $savRow[$colSavValue]) === ''
                    ) {
                        $savMap[$id] = '#N/A';
                    } else {
                        $savMap[$id] = $savRow[$colSavValue];
                    }
                }
            }
        }

        // =========================================================
        // 4. LECTURE DU 3ÈME FICHIER (PBO)
        // =========================================================

        $thirdRows = $request->hasFile('third_file')
            ? $this->readFirstSheet($request->file('third_file'))
            : [];

        $thirdMap = [];

        if (!empty($thirdRows)) {

            $thirdHeader = array_map(
                fn($v) => trim((string) $v),
                $thirdRows[0]
            );

            $colThirdIdRdv = array_search(
                'Intervention',
                $thirdHeader,
                true
            );

            if ($colThirdIdRdv !== false) {

                $colThirdValue = $colThirdIdRdv + 1;

                foreach (
                    array_slice($thirdRows, 1) as $thirdRow
                ) {

                    $id = isset($thirdRow[$colThirdIdRdv])
                        ? trim((string) $thirdRow[$colThirdIdRdv])
                        : '';

                    if ($id === '') {
                        continue;
                    }

                    if (
                        !array_key_exists(
                            $colThirdValue,
                            $thirdRow
                        ) ||
                        $thirdRow[$colThirdValue] === null ||
                        trim((string) $thirdRow[$colThirdValue]) === ''
                    ) {
                        $thirdMap[$id] = '#N/A';
                    } else {
                        $thirdMap[$id] =
                            $thirdRow[$colThirdValue];
                    }
                }
            }
        }

        // Libérer la mémoire (plus utiles après construction des maps)
        unset($savRows, $thirdRows);

        // =========================================================
        // 5. HEADER DU FICHIER PRINCIPAL
        // =========================================================

        $originalHeader = array_map(
            fn($v) => trim((string) $v),
            $primaryRows[0]
        );

        // =========================================================
        // 6. RECHERCHE DES COLONNES INITIALES
        // =========================================================

        $colInstallation = array_search(
            'INSTALLATION',
            $originalHeader,
            true
        );

        $colDeplacement = array_search(
            'DEPLACEMENT',
            $originalHeader,
            true
        );

        $colCodeResultat = array_search(
            'CODE_RESULTAT',
            $originalHeader,
            true
        );

        $colTotal = array_search(
            'TOTAL',
            $originalHeader,
            true
        );

        $colIdRdv = array_search(
            'CODE_INTER',
            $originalHeader,
            true
        );

        $colCodePartenaire = array_search(
            'CODE_PARTENAIRE',
            $originalHeader,
            true
        );

        if (
            $colInstallation === false ||
            $colDeplacement === false ||
            $colCodeResultat === false ||
            $colTotal === false ||
            $colIdRdv === false
        ) {
            return back()->with(
                'error',
                'Les colonnes CODE_INTER, INSTALLATION, DEPLACEMENT, CODE_RESULTAT et TOTAL sont obligatoires.'
            );
        }

        // =========================================================
        // 7. CRÉATION DU HEADER FINAL
        // =========================================================

        $specialColumns = [
            'Non Facturable',
            'SAV MC',
            'PBO_PHOTO_NACELLE'
        ];

        $cleanHeader = [];

        foreach ($originalHeader as $oldIndex => $columnName) {

            if (
                !in_array(
                    $columnName,
                    $specialColumns,
                    true
                )
            ) {
                $cleanHeader[] = [
                    'name'  => $columnName,
                    'index' => $oldIndex,
                ];
            }
        }

        $header = [];

        foreach ($cleanHeader as $column) {
            $header[] = $column['name'];
        }

        // Ajouter les nouvelles colonnes APRÈS TOTAL
        $header[] = 'Non Facturable';
        $header[] = 'SAV MC';
        $header[] = 'PBO_PHOTO_NACELLE';

        // =========================================================
        // 8. RECONSTRUCTION DES LIGNES
        // =========================================================

        $rows = [];
        $rows[] = $header;

        foreach (
            array_slice($primaryRows, 1) as $oldRow
        ) {

            $newRow = [];

            foreach ($cleanHeader as $column) {
                $oldIndex = $column['index'];
                $newRow[] = $oldRow[$oldIndex] ?? '';
            }

            // Nouvelles colonnes après TOTAL
            $newRow[] = ''; // Non Facturable
            $newRow[] = ''; // SAV MC
            $newRow[] = ''; // PBO_PHOTO_NACELLE

            $rows[] = $newRow;
        }

        $primaryRows = $rows;

        // =========================================================
        // 9. NOUVEAUX INDEX
        // =========================================================

        $colInstallation  = array_search('INSTALLATION', $header, true);
        $colDeplacement   = array_search('DEPLACEMENT', $header, true);
        $colCodeResultat  = array_search('CODE_RESULTAT', $header, true);
        $colTotal         = array_search('TOTAL', $header, true);
        $colIdRdv         = array_search('CODE_INTER', $header, true);
        $colCodePartenaire = array_search('CODE_PARTENAIRE', $header, true);
        $colNonFacturable = array_search('Non Facturable', $header, true);
        $colSavMc         = array_search('SAV MC', $header, true);
        $colPbo           = array_search('PBO_PHOTO_NACELLE', $header, true);

        // =========================================================
        // 10. SUPPRESSION DES LIGNES SANS CODE_INTER
        // =========================================================

        $cleanRows   = [];
        $cleanRows[] = $header;

        foreach (array_slice($primaryRows, 1) as $row) {
            $idRdv = isset($row[$colIdRdv]) ? trim((string) $row[$colIdRdv]) : '';

            if ($idRdv === '') {
                continue;
            }

            $cleanRows[] = $row;
        }

        $primaryRows = $cleanRows;

        // =========================================================
        // 11. RÈGLE 1
        // =========================================================

        foreach ($primaryRows as $index => &$row) {
            if ($index === 0) {
                continue;
            }

            if ((float) $row[$colInstallation] == 35.4) {
                $row[$colInstallation] = 0;

                if ((float) $row[$colDeplacement] == 0) {
                    $row[$colDeplacement] = 49;
                }
            }
        }
        unset($row);

        // =========================================================
        // 12. RÈGLE 2
        // =========================================================

        foreach ($primaryRows as $index => &$row) {
            if ($index === 0) {
                continue;
            }

            if ((float) $row[$colInstallation] == 59) {
                $row[$colInstallation] = 68;
            }
        }
        unset($row);

        // =========================================================
        // CODES NON FACTURABLES
        // =========================================================

        $nonFacturableCodes = [
            'SNR4e',
            'SNR4f',
            'INR1c',
            'INR1b',
            'INR1a',
            'INR2c',
            'INR2b',
            'SNR4g',
        ];

        // =========================================================
        // 13. RÈGLE 3
        // =========================================================

        foreach ($primaryRows as $index => &$row) {
            if ($index === 0) {
                continue;
            }

            $codeResultat = isset($row[$colCodeResultat])
                ? trim((string) $row[$colCodeResultat])
                : '';

            if (
                (float) $row[$colInstallation] > 68 &&
                $codeResultat !== 'RET1b' &&
                !in_array($codeResultat, $nonFacturableCodes, true)
            ) {
                $row[$colInstallation] = 68;
            }
        }
        unset($row);

        // =========================================================
        // 14. RÈGLE 4
        // =========================================================

        foreach ($primaryRows as $index => &$row) {
            if ($index === 0) {
                continue;
            }

            $codeResultat = isset($row[$colCodeResultat])
                ? trim((string) $row[$colCodeResultat])
                : '';

            if (in_array($codeResultat, $nonFacturableCodes, true)) {
                $row[$colNonFacturable] = $codeResultat;
            } else {
                $row[$colNonFacturable] = '#N/A';
            }
        }
        unset($row);

        // =========================================================
        // 15. RÈGLE 6
        // =========================================================

        foreach ($primaryRows as $index => &$row) {
            if ($index === 0) {
                continue;
            }

            // On vérifie INSTALLATION et DEPLACEMENT,
            // et non plus TOTAL.
            if (
                $row[$colNonFacturable] === '#N/A' &&
                (float) $row[$colInstallation] == 0 &&
                (float) $row[$colDeplacement] == 0
            ) {
                $row[$colInstallation] = 68;

              
            }
        }
        unset($row);

        // =========================================================
        // 16. RÈGLE 5
        // =========================================================

        // Colonnes de prix à remettre à 0 pour les lignes non facturables
        $zeroColumns = [];

        foreach ([
            'GOULOTTES_EN_METRE',
            'PRIX_HT_GOULOTTE',
            'ETH_EN_METRE',
            'PRIX_HT_ETH',
            'INSTALLATION',
            'MES',
            'MATERIEL',
            'SUPPORT',
            'LOGISTIQUE',
            'DEPLACEMENT',
            'TOTAL',
        ] as $zeroColName) {
            $zeroColIndex = array_search($zeroColName, $header, true);

            if ($zeroColIndex !== false) {
                $zeroColumns[] = $zeroColIndex;
            }
        }

        foreach ($primaryRows as $index => &$row) {
            if ($index === 0) {
                continue;
            }

            if ($row[$colNonFacturable] !== '#N/A') {
                foreach ($zeroColumns as $zeroColIndex) {
                    if (
                        isset($row[$zeroColIndex]) &&
                        is_numeric($row[$zeroColIndex]) &&
                        (float) $row[$zeroColIndex] != 0
                    ) {
                        $row[$zeroColIndex] = 0;
                    }
                }

                $row[$colInstallation] = 0;
                $row[$colDeplacement]  = 0;
                $row[$colTotal]        = 0;
            }
        }
        unset($row);

        // =========================================================
        // 17. RET4a
        // =========================================================

        foreach ($primaryRows as $index => &$row) {
            if ($index === 0) {
                continue;
            }

            $codeResultat = isset($row[$colCodeResultat])
                ? trim((string) $row[$colCodeResultat])
                : '';

            if ($codeResultat === 'RET4a') {
                if (
                    is_numeric($row[$colDeplacement]) &&
                    (float) $row[$colDeplacement] == 30
                ) {
                    $row[$colInstallation] = 0;
                } elseif (
                    is_numeric($row[$colDeplacement]) &&
                    (float) $row[$colDeplacement] == 0
                ) {
                    $row[$colInstallation] = 0;
                    $row[$colDeplacement]  = 49;
                }
            }
        }
        unset($row);

        // =========================================================
        // 18. RÈGLE 7 (SAV MC + PBO)
        // =========================================================

        foreach ($primaryRows as $index => &$row) {
            if ($index === 0) {
                continue;
            }

            $idRdv = isset($row[$colIdRdv])
                ? trim((string) $row[$colIdRdv])
                : '';

            // SAV MC
            if ($idRdv !== '' && array_key_exists($idRdv, $savMap)) {
                $savValue = $savMap[$idRdv];

                $row[$colSavMc] =
                    ($savValue === null || trim((string) $savValue) === '')
                        ? '#N/A'
                        : $savValue;
            } else {
                $row[$colSavMc] = '#N/A';
            }

            // PBO
            if ($idRdv !== '' && array_key_exists($idRdv, $thirdMap)) {
                $pboValue = $thirdMap[$idRdv];

                $row[$colPbo] =
                    ($pboValue === null || trim((string) $pboValue) === '')
                        ? '#N/A'
                        : $pboValue;
            } else {
                $row[$colPbo] = '#N/A';
            }
        }
        unset($row);

        // =========================================================
        // 19. IMC + SAV MC
        // =========================================================

        foreach ($primaryRows as $index => &$row) {
            if ($index === 0) {
                continue;
            }

            $codePartenaire =
                ($colCodePartenaire !== false && isset($row[$colCodePartenaire]))
                    ? strtoupper(trim((string) $row[$colCodePartenaire]))
                    : '';

            if (str_contains($codePartenaire, 'IMC')) {

                // Toutes les lignes IMC : DEPLACEMENT = 0
                $row[$colDeplacement] = 0;

                if ($row[$colSavMc] === '#N/A') {
                    $row[$colSavMc] = 0;
                }

                if (is_numeric($row[$colSavMc])) {
                    $sav = (float) $row[$colSavMc];

                    if ($sav >= 0 && $sav <= 1) {
                        $row[$colInstallation] = 68;
                    } elseif ($sav >= 2 && $sav <= 3) {
                        $row[$colInstallation] = 110;
                    } elseif ($sav >= 4 && $sav <= 9) {
                        $row[$colInstallation] = 180;
                    } elseif ($sav >= 10 && $sav < 32) {
                        $row[$colInstallation] = 220;
                    } elseif ($sav >= 32 && $sav <= 64) {
                        $row[$colInstallation] = 580;
                    }
                }
            }
        }
        unset($row);

        // =========================================================
        // 20. PBO
        // =========================================================

        foreach ($primaryRows as $index => &$row) {
            if ($index === 0) {
                continue;
            }

            $pbo = isset($row[$colPbo])
                ? trim((string) $row[$colPbo])
                : '';

            if (
                $pbo !== '' &&
                strtoupper($pbo) !== '#N/A'
            ) {
                if (
                    is_numeric($row[$colInstallation]) &&
                    (float) $row[$colInstallation] == 68
                ) {
                    $row[$colInstallation] = 113;
                }
            }
        }
        unset($row);

        // =========================================================
        // 21. CONTRÔLE FINAL & TOTAL
        // =========================================================

        // Colonnes dont la somme donne le TOTAL
        $sumColumns = [];

        foreach ([
            'GOULOTTES_EN_METRE',
            'PRIX_HT_GOULOTTE',
            'ETH_EN_METRE',
            'PRIX_HT_ETH',
            'INSTALLATION',
            'MES',
            'MATERIEL',
            'SUPPORT',
            'LOGISTIQUE',
            'DEPLACEMENT',
        ] as $sumColName) {
            $sumColIndex = array_search(
                $sumColName,
                $header,
                true
            );

            if ($sumColIndex !== false) {
                $sumColumns[] = $sumColIndex;
            }
        }

        $finalRows   = [];
        $finalRows[] = $header;

        foreach (array_slice($primaryRows, 1) as $row) {

            while (count($row) < count($header)) {
                $row[] = '';
            }

            if (count($row) > count($header)) {
                $row = array_slice(
                    $row,
                    0,
                    count($header)
                );
            }

            $idRdv = isset($row[$colIdRdv])
                ? trim((string) $row[$colIdRdv])
                : '';

            if ($idRdv === '') {
                continue;
            }

            if (
                !isset($row[$colInstallation]) ||
                trim((string) $row[$colInstallation]) === '' ||
                !is_numeric($row[$colInstallation])
            ) {
                $row[$colInstallation] = 0;
            }

            if (
                !isset($row[$colDeplacement]) ||
                trim((string) $row[$colDeplacement]) === '' ||
                !is_numeric($row[$colDeplacement])
            ) {
                $row[$colDeplacement] = 0;
            }

            if (
                !isset($row[$colSavMc]) ||
                trim((string) $row[$colSavMc]) === ''
            ) {
                $row[$colSavMc] = '#N/A';
            }

            if (
                !isset($row[$colPbo]) ||
                trim((string) $row[$colPbo]) === ''
            ) {
                $row[$colPbo] = '#N/A';
            }

            if (
                !isset($row[$colNonFacturable]) ||
                trim((string) $row[$colNonFacturable]) === ''
            ) {
                $row[$colNonFacturable] = '#N/A';
            }

            // Calcul du TOTAL = somme de toutes les colonnes de prix
            $totalLigne = 0;

            foreach ($sumColumns as $sumColIndex) {
                if (
                    isset($row[$sumColIndex]) &&
                    is_numeric($row[$sumColIndex])
                ) {
                    $totalLigne += (float) $row[$sumColIndex];
                }
            }

            $row[$colTotal] = $totalLigne;

            $finalRows[] = $row;
        }

        $primaryRows = $finalRows;

        // =========================================================
        // 22. EXPORT EXCEL
        // =========================================================

        $fileName =
            'facture_complete_' .
            date('Y-m-d_H-i-s') .
            '.xlsx';

        // Colonnes au format 0.00
        // (INSTALLATION, DEPLACEMENT, TOTAL)
        $numericCols = [
            $colInstallation,
            $colDeplacement,
            $colTotal
        ];

        return response()->streamDownload(
            function () use ($primaryRows, $numericCols) {

                $writer = new Writer();
                $writer->openToFile('php://output');

                $numStyle = (new Style())
                    ->setFormat('0.00');

                foreach ($primaryRows as $i => $row) {

                    // Ligne d'en-tête
                    if ($i === 0) {
                        $writer->addRow(
                            Row::fromValues($row)
                        );
                        continue;
                    }

                    $cells = [];

                    foreach ($row as $k => $value) {
                        $cells[] = in_array(
                            $k,
                            $numericCols,
                            true
                        )
                            ? Cell::fromValue(
                                $value,
                                $numStyle
                            )
                            : Cell::fromValue($value);
                    }

                    $writer->addRow(
                        new Row($cells)
                    );
                }

                $writer->close();

            },
            $fileName,
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }
}