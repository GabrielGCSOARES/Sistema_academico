<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use App\Models\Sala;
use App\Models\Alocacao;
use App\Models\Docente;
use App\Models\Disciplina;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DirectorController extends Controller
{
    private const DIAS_SEMANA = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta'];

    private const HORARIOS_AULA = [
        ['inicio' => '07:40', 'fim' => '08:30'],
        ['inicio' => '08:30', 'fim' => '09:20'],
        ['inicio' => '09:30', 'fim' => '10:20'],
        ['inicio' => '10:20', 'fim' => '11:10'],
        ['inicio' => '11:20', 'fim' => '12:10'],
        ['inicio' => '12:10', 'fim' => '13:00'],
        ['inicio' => '14:00', 'fim' => '14:50'],
        ['inicio' => '14:50', 'fim' => '15:40'],
        ['inicio' => '15:50', 'fim' => '16:40'],
        ['inicio' => '16:40', 'fim' => '17:30'],
        ['inicio' => '17:30', 'fim' => '18:20'],
        ['inicio' => '19:00', 'fim' => '19:50'],
        ['inicio' => '19:50', 'fim' => '20:40'],
        ['inicio' => '20:50', 'fim' => '21:40'],
        ['inicio' => '21:40', 'fim' => '22:30'],
    ];

    private const SALAS_SEED = [
        ['nome' => 'Sala 0', 'predio' => 'Prédio 1', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 01', 'predio' => 'Prédio 1', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 02', 'predio' => 'Prédio 1', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 03', 'predio' => 'Prédio 1', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 04', 'predio' => 'Prédio 1', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 05', 'predio' => 'Prédio 1', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 06', 'predio' => 'Prédio 1', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 07', 'predio' => 'Prédio 1', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 08', 'predio' => 'Prédio 1', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 09', 'predio' => 'Prédio 1', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 10', 'predio' => 'Prédio 1', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 11', 'predio' => 'Prédio 1', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 12', 'predio' => 'Prédio 1', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 13', 'predio' => 'Prédio 1', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Multi 01', 'predio' => 'Prédio 1', 'capacidade' => 20, 'tipo' => 'Laboratório'],
        ['nome' => 'Multi 02', 'predio' => 'Prédio 1', 'capacidade' => 20, 'tipo' => 'Laboratório'],
        ['nome' => 'Multi 07', 'predio' => 'Prédio 1', 'capacidade' => 20, 'tipo' => 'Laboratório'],
        ['nome' => 'Sala 01', 'predio' => 'Prédio 2', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 02', 'predio' => 'Prédio 2', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 03', 'predio' => 'Prédio 2', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 04', 'predio' => 'Prédio 2', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 05', 'predio' => 'Prédio 2', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 06', 'predio' => 'Prédio 2', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Sala 07', 'predio' => 'Prédio 2', 'capacidade' => 40, 'tipo' => 'Sala'],
        ['nome' => 'Auditório', 'predio' => 'Prédio 2', 'capacidade' => 100, 'tipo' => 'Auditório'],
    ];

    public function getProfessores()
    {
        $this->sincronizarProfessoresComDocentes();

        $professores = Professor::where('ativo', true)
            ->whereIn('id', Docente::query()->select('id'))
            ->with(['alocacoesAtivas'])
            ->orderBy('nome')
            ->get()
            ->map(function ($professor) {
                return [
                    'id' => $professor->id,
                    'nome' => $professor->nome,
                    'disciplina' => $professor->disciplina ?: 'Sem disciplina',
                    'curso' => $professor->curso,
                    'ativo' => (bool) $professor->ativo,
                    'alocacoes_ativas' => $professor->alocacoesAtivas->values(),
                    'alocacoesAtivas' => $professor->alocacoesAtivas->values(),
                ];
            });

        return response()->json($professores);
    }


    public function getSalas()
    {
        $this->sincronizarSalasComSeeder();

        $salas = Sala::where('ativa', true)
            ->with(['alocacoesAtivas.professor'])
            ->orderBy('predio')
            ->orderBy('nome')
            ->get();
        
        return response()->json($salas);
    }

    public function getAlocacoesAtuais()
    {
        $horarioAtual = now()->format('H:i:s');

        $alocacoes = Alocacao::with(['professor', 'sala'])
            ->whereIn('professor_id', Docente::query()->select('id'))
            ->where('data', now()->toDateString())
            ->where('horario_inicio', '<=', $horarioAtual)
            ->where(function($query) use ($horarioAtual) {
                $query->whereNull('horario_fim')
                      ->orWhere('horario_fim', '>=', $horarioAtual);
            })
            ->get();
        
        return response()->json($alocacoes);
    }

    public function getAlocacoesSemana(Request $request)
    {
        $inicioSemana = $request->filled('inicio')
            ? Carbon::parse($request->inicio)->startOfDay()
            : now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $fimSemana = (clone $inicioSemana)->addDays(4)->endOfDay();

        $alocacoes = Alocacao::with(['professor', 'sala'])
            ->whereIn('professor_id', Docente::query()->select('id'))
            ->whereBetween('data', [$inicioSemana->toDateString(), $fimSemana->toDateString()])
            ->get();

        return response()->json($alocacoes);
    }

    public function exportarXlsx()
    {
        $this->sincronizarProfessoresComDocentes();

        $inicioSemana = request()->filled('inicio')
            ? Carbon::parse(request()->inicio)->startOfDay()
            : now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $fimSemana = (clone $inicioSemana)->addDays(4)->endOfDay();

        $alocacoes = Alocacao::with(['professor', 'sala'])
            ->whereIn('professor_id', Docente::query()->select('id'))
            ->whereBetween('data', [$inicioSemana->toDateString(), $fimSemana->toDateString()])
            ->orderBy('data')
            ->orderBy('horario_inicio')
            ->get();

        $content = $this->buildXlsxContent($alocacoes, $inicioSemana);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="alocacoes.xlsx"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    public function exportarProfessoresLecionandoXlsx(Request $request)
    {
        $this->sincronizarProfessoresComDocentes();

        $inicioSemana = $request->filled('inicio')
            ? Carbon::parse($request->inicio)->startOfDay()
            : now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $fimSemana = (clone $inicioSemana)->addDays(4)->endOfDay();

        $query = Alocacao::with(['professor', 'sala'])
            ->whereIn('professor_id', Docente::query()->select('id'))
            ->whereBetween('data', [$inicioSemana->toDateString(), $fimSemana->toDateString()]);

        if ($request->filled('professor_id')) {
            $query->where('professor_id', $request->integer('professor_id'));
        }

        $alocacoes = $query
            ->orderBy('data')
            ->orderBy('horario_inicio')
            ->get();

        $rows = [[
            'Professor',
            'Dia',
            'Sala',
            'Prédio',
            'Horário',
            'Matéria',
        ]];

        foreach ($alocacoes as $alocacao) {
            $rows[] = [
                $alocacao->professor?->nome ?? 'Sem professor',
                $alocacao->data?->format('d/m/Y') ?? '-',
                $alocacao->sala?->nome ?? 'Sem sala',
                $alocacao->predio ?? 'Sem prédio',
                $this->formatHorario($alocacao),
                $alocacao->professor?->disciplina ?? 'Sem disciplina',
            ];
        }

        $content = $this->buildTableXlsxContent($rows, 'Professores lecionando');

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="professores-lecionando.xlsx"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    private function buildXlsxContent($alocacoes, Carbon $inicioSemana): string
    {
        $rows = [];
        $rows[] = [
            ['value' => 'Alocações da semana', 'style' => 1],
            ['value' => '', 'style' => 1],
            ['value' => '', 'style' => 1],
            ['value' => '', 'style' => 1],
            ['value' => '', 'style' => 1],
            ['value' => '', 'style' => 1],
        ];

        $cabecalho = [['value' => 'Horário', 'style' => 2]];
        foreach (self::DIAS_SEMANA as $indiceDia => $dia) {
            $data = (clone $inicioSemana)->addDays($indiceDia)->format('d/m/Y');
            $cabecalho[] = ['value' => "{$dia}\n{$data}", 'style' => 2];
        }
        $rows[] = $cabecalho;

        foreach (self::HORARIOS_AULA as $horario) {
            $row = [[
                'value' => "{$horario['inicio']} - {$horario['fim']}",
                'style' => 3,
            ]];

            foreach (self::DIAS_SEMANA as $indiceDia => $_dia) {
                $data = (clone $inicioSemana)->addDays($indiceDia)->toDateString();
                $conteudo = $this->formatarAlocacoesDoHorario($alocacoes, $data, $horario);

                $row[] = [
                    'value' => $conteudo,
                    'style' => $conteudo ? 4 : 5,
                ];
            }

            $rows[] = $row;
        }

        $sheetRows = $this->buildSheetRows($rows);
        $ultimaLinha = count($rows);

        $worksheet = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
           xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <dimension ref="A1:F{{ultimaLinha}}"/>
    <cols>
        <col min="1" max="1" width="18" customWidth="1"/>
        <col min="2" max="6" width="34" customWidth="1"/>
    </cols>
    <sheetData>
        {{rows}}
    </sheetData>
    <mergeCells count="1"><mergeCell ref="A1:F1"/></mergeCells>
    <autoFilter ref="A2:F{{ultimaLinha}}"/>
</worksheet>
XML;

        $styles = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="3">
    <font><sz val="11"/><name val="Calibri"/></font>
    <font><b/><sz val="16"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>
    <font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>
  </fonts>
  <fills count="5">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF1F4E79"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFEAF2F8"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/><bgColor indexed="64"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border/>
    <border>
      <left style="thin"><color rgb="FFB7C9D6"/></left>
      <right style="thin"><color rgb="FFB7C9D6"/></right>
      <top style="thin"><color rgb="FFB7C9D6"/></top>
      <bottom style="thin"><color rgb="FFB7C9D6"/></bottom>
    </border>
  </borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="6">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyFill="1" applyBorder="1"/>
  </cellXfs>
  <cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>
XML;

        $contentTypes = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>
XML;

        $workbook = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Alocações" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML;

        $workbookRels = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;

        $rels = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;

        $worksheet = str_replace(
            ['{{rows}}', '{{ultimaLinha}}'],
            [implode('', $sheetRows), (string) $ultimaLinha],
            $worksheet
        );

        return $this->buildZipArchive([
            '[Content_Types].xml' => $contentTypes,
            '_rels/.rels' => $rels,
            'xl/workbook.xml' => $workbook,
            'xl/_rels/workbook.xml.rels' => $workbookRels,
            'xl/styles.xml' => $styles,
            'xl/worksheets/sheet1.xml' => $worksheet,
        ]);
    }

    private function buildSheetRows(array $rows): array
    {
        $sheetRows = [];

        foreach ($rows as $rowIndex => $row) {
            $cells = [];

            foreach ($row as $columnIndex => $cell) {
                $cellReference = $this->columnName($columnIndex + 1) . ($rowIndex + 1);
                $normalizedValue = $cell['value'] === null ? '' : (string) $cell['value'];
                $style = (int) ($cell['style'] ?? 0);
                $cells[] = '<c r="' . $cellReference . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . htmlspecialchars($normalizedValue, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</t></is></c>';
            }

            $height = $rowIndex === 0 ? '24' : ($rowIndex === 1 ? '36' : '54');
            $sheetRows[] = '<row r="' . ($rowIndex + 1) . '" ht="' . $height . '" customHeight="1">' . implode('', $cells) . '</row>';
        }

        return $sheetRows;
    }

    private function buildTableXlsxContent(array $rows, string $title): string
    {
        $styledRows = [[
            ['value' => $title, 'style' => 1],
            ['value' => '', 'style' => 1],
            ['value' => '', 'style' => 1],
            ['value' => '', 'style' => 1],
            ['value' => '', 'style' => 1],
            ['value' => '', 'style' => 1],
        ]];

        foreach ($rows as $rowIndex => $row) {
            $styledRows[] = array_map(fn ($value) => [
                'value' => $value,
                'style' => $rowIndex === 0 ? 2 : 5,
            ], $row);
        }

        $sheetRows = $this->buildSheetRows($styledRows);
        $ultimaLinha = count($styledRows);

        $worksheet = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
           xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <dimension ref="A1:F{{ultimaLinha}}"/>
    <cols>
        <col min="1" max="1" width="36" customWidth="1"/>
        <col min="2" max="2" width="16" customWidth="1"/>
        <col min="3" max="4" width="18" customWidth="1"/>
        <col min="5" max="5" width="18" customWidth="1"/>
        <col min="6" max="6" width="34" customWidth="1"/>
    </cols>
    <sheetData>
        {{rows}}
    </sheetData>
    <mergeCells count="1"><mergeCell ref="A1:F1"/></mergeCells>
    <autoFilter ref="A2:F{{ultimaLinha}}"/>
</worksheet>
XML;

        $worksheet = str_replace(
            ['{{rows}}', '{{ultimaLinha}}'],
            [implode('', $sheetRows), (string) $ultimaLinha],
            $worksheet
        );

        return $this->buildZipArchive([
            '[Content_Types].xml' => $this->contentTypesXml(),
            '_rels/.rels' => $this->rootRelationshipsXml(),
            'xl/workbook.xml' => $this->workbookXml(),
            'xl/_rels/workbook.xml.rels' => $this->workbookRelationshipsXml(),
            'xl/styles.xml' => $this->stylesXml(),
            'xl/worksheets/sheet1.xml' => $worksheet,
        ]);
    }

    private function formatarAlocacoesDoHorario($alocacoes, string $data, array $horario): string
    {
        $inicioSlot = $this->timeToMinutes($horario['inicio']);
        $fimSlot = $this->timeToMinutes($horario['fim']);

        return $alocacoes
            ->filter(function ($alocacao) use ($data, $inicioSlot, $fimSlot) {
                $inicio = $this->timeToMinutes($alocacao->horario_inicio?->format('H:i') ?? '00:00');
                $fim = $alocacao->horario_fim
                    ? $this->timeToMinutes($alocacao->horario_fim->format('H:i'))
                    : $inicio + 50;

                return $alocacao->data?->toDateString() === $data
                    && $inicio < $fimSlot
                    && $fim > $inicioSlot;
            })
            ->map(function ($alocacao) {
                $horarioInicio = $alocacao->horario_inicio ? $alocacao->horario_inicio->format('H:i') : '-';
                $horarioFim = $alocacao->horario_fim ? $alocacao->horario_fim->format('H:i') : '-';

                return implode("\n", [
                    $alocacao->professor?->nome ?? 'Sem professor',
                    $alocacao->professor?->disciplina ?? 'Sem disciplina',
                    ($alocacao->predio ?? 'Sem prédio') . ' - ' . ($alocacao->sala?->nome ?? 'Sem sala'),
                    $horarioInicio . ' às ' . $horarioFim,
                ]);
            })
            ->implode("\n\n");
    }

    private function formatHorario(Alocacao $alocacao): string
    {
        $inicio = $alocacao->horario_inicio ? $alocacao->horario_inicio->format('H:i') : '-';
        $fim = $alocacao->horario_fim ? $alocacao->horario_fim->format('H:i') : '-';

        return "{$inicio} às {$fim}";
    }

    private function stylesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="3">
    <font><sz val="11"/><name val="Calibri"/></font>
    <font><b/><sz val="16"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>
    <font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>
  </fonts>
  <fills count="5">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF1F4E79"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFEAF2F8"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/><bgColor indexed="64"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border/>
    <border>
      <left style="thin"><color rgb="FFB7C9D6"/></left>
      <right style="thin"><color rgb="FFB7C9D6"/></right>
      <top style="thin"><color rgb="FFB7C9D6"/></top>
      <bottom style="thin"><color rgb="FFB7C9D6"/></bottom>
    </border>
  </borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="6">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyFill="1" applyBorder="1"/>
  </cellXfs>
  <cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>
XML;
    }

    private function contentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>
XML;
    }

    private function workbookXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Alocações" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML;
    }

    private function workbookRelationshipsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;
    }

    private function rootRelationshipsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    private function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', substr($time, 0, 5)));

        return ($hours * 60) + $minutes;
    }

    private function buildZipArchive(array $files): string
    {
        $localFiles = '';
        $centralDirectory = '';
        $offset = 0;
        $dosTime = (0 << 11) | (0 << 5) | 0;
        $dosDate = ((2026 - 1980) << 9) | (1 << 5) | 1;

        foreach ($files as $path => $contents) {
            $crc = crc32($contents);
            if ($crc < 0) {
                $crc += 4294967296;
            }

            $size = strlen($contents);
            $pathLength = strlen($path);

            $localHeader = pack(
                'VvvvvvVVVvv',
                0x04034b50,
                20,
                0,
                0,
                $dosTime,
                $dosDate,
                $crc,
                $size,
                $size,
                $pathLength,
                0
            );

            $localFiles .= $localHeader . $path . $contents;

            $centralDirectory .= pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50,
                20,
                20,
                0,
                0,
                $dosTime,
                $dosDate,
                $crc,
                $size,
                $size,
                $pathLength,
                0,
                0,
                0,
                0,
                0,
                $offset
            ) . $path;

            $offset += strlen($localHeader) + $pathLength + $size;
        }

        $centralDirectorySize = strlen($centralDirectory);
        $centralDirectoryOffset = strlen($localFiles);
        $fileCount = count($files);

        $endOfCentralDirectory = pack(
            'VvvvvVVv',
            0x06054b50,
            0,
            0,
            $fileCount,
            $fileCount,
            $centralDirectorySize,
            $centralDirectoryOffset,
            0
        );

        return $localFiles . $centralDirectory . $endOfCentralDirectory;
    }

    private function columnName(int $index): string
    {
        $letters = '';
        while ($index > 0) {
            $index--;
            $letters = chr(65 + ($index % 26)) . $letters;
            $index = intdiv($index, 26);
        }

        return $letters;
    }

    private function sincronizarProfessoresComDocentes(): void
    {
        Docente::with('disciplina')->get()->each(function (Docente $docente) {
            $professor = Professor::firstOrNew(['id' => $docente->id]);

            $professor->forceFill([
                'nome' => $docente->nome,
                'email' => $professor->email ?: sprintf('professor%d@fatec.edu', $docente->id),
                'disciplina' => $docente->disciplina?->nome ?: 'Sem disciplina',
                'curso' => 'Não informado',
                'ativo' => true,
            ])->save();
        });
    }

    private function sincronizarSalasComSeeder(): void
    {
        if (!Schema::hasColumn('salas', 'predio') || !Schema::hasColumn('salas', 'tipo')) {
            return;
        }

        foreach (self::SALAS_SEED as $dadosSala) {
            Sala::updateOrCreate(
                [
                    'nome' => $dadosSala['nome'],
                    'predio' => $dadosSala['predio'],
                ],
                [
                    'capacidade' => $dadosSala['capacidade'],
                    'tipo' => $dadosSala['tipo'],
                    'ativa' => true,
                ]
            );
        }

        Sala::query()
            ->where(function ($query) {
                foreach (self::SALAS_SEED as $dadosSala) {
                    $query->where(function ($item) use ($dadosSala) {
                        $item
                            ->where('nome', '!=', $dadosSala['nome'])
                            ->orWhere('predio', '!=', $dadosSala['predio']);
                    });
                }
            })
            ->update(['ativa' => false]);
    }

    public function alocarProfessor(Request $request)
    {
        $this->sincronizarProfessoresComDocentes();

        $request->validate([
            'professor_id' => 'required|exists:professores,id',
            'sala_id' => 'required|exists:salas,id',
            'predio' => 'nullable|string|max:50',
            'data' => 'required|date',
            'horario_atual' => 'nullable',
            'horario_inicio' => 'nullable',
            'horario_fim' => 'nullable'
        ]);

        try {
            DB::beginTransaction();

            if (!Docente::whereKey($request->professor_id)->exists()) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Professor não faz parte dos docentes cadastrados no seeder.'
                ], 422);
            }

            $predio = $request->predio ?? 'Prédio 1';
            $horarioBase = $request->horario_inicio ?? $request->horario_atual;

            if (!$horarioBase) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Horário inicial é obrigatório.'
                ], 422);
            }

            $horarioInicio = date('H:i:s', strtotime($horarioBase));
            $horarioFim = $request->filled('horario_fim')
                ? date('H:i:s', strtotime($request->horario_fim))
                : null;

            // Verificar se o professor já está alocado neste intervalo
            $professorAlocado = Alocacao::where('professor_id', $request->professor_id)
                ->whereIn('professor_id', Docente::query()->select('id'))
                ->where('data', $request->data)
                ->where(function($query) use ($horarioInicio, $horarioFim) {
                    if ($horarioFim) {
                        $query->where('horario_inicio', '<', $horarioFim)
                              ->where(function($q) use ($horarioInicio) {
                                  $q->whereNull('horario_fim')
                                    ->orWhere('horario_fim', '>', $horarioInicio);
                              });
                    } else {
                        $query->where('horario_inicio', '<=', $horarioInicio)
                              ->where(function($q) use ($horarioInicio) {
                                  $q->whereNull('horario_fim')
                                    ->orWhere('horario_fim', '>=', $horarioInicio);
                              });
                    }
                })
                ->lockForUpdate()
                ->first();

            if ($professorAlocado) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Este professor já está lecionando em outra sala neste horário.',
                    'alocacao_atual' => $professorAlocado
                ], 409);
            }

            // Verificar se a sala já está ocupada neste prédio e intervalo
            $salaOcupada = Alocacao::where('sala_id', $request->sala_id)
                ->whereIn('professor_id', Docente::query()->select('id'))
                ->where('predio', $predio)
                ->where('data', $request->data)
                ->where(function($query) use ($horarioInicio, $horarioFim) {
                    if ($horarioFim) {
                        $query->where('horario_inicio', '<', $horarioFim)
                              ->where(function($q) use ($horarioInicio) {
                                  $q->whereNull('horario_fim')
                                    ->orWhere('horario_fim', '>', $horarioInicio);
                              });
                    } else {
                        $query->where('horario_inicio', '<=', $horarioInicio)
                              ->where(function($q) use ($horarioInicio) {
                                  $q->whereNull('horario_fim')
                                    ->orWhere('horario_fim', '>=', $horarioInicio);
                              });
                    }
                })
                ->lockForUpdate()
                ->first();

            if ($salaOcupada) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Esta sala já está ocupada neste horário.'
                ], 423);
            }

            // Criar a alocação
            $alocacao = Alocacao::create([
                'professor_id' => $request->professor_id,
                'sala_id' => $request->sala_id,
                'predio' => $predio,
                'data' => $request->data,
                'horario_inicio' => $horarioInicio,
                'horario_fim' => $horarioFim,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Professor alocado com sucesso!',
                'alocacao' => $alocacao->load(['professor', 'sala'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao processar alocação: ' . $e->getMessage()
            ], 500);
        }
    }

    public function desalocarProfessor($id)
    {
        try {
            $alocacao = Alocacao::findOrFail($id);
            $alocacao->delete();

            return response()->json([
                'message' => 'Professor removido da alocação com sucesso.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao desalocar professor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verificarProfessor($professorId)
    {
        try {
            $professor = Professor::findOrFail($professorId);
            $horarioAtual = now()->format('H:i:s');
            
            $alocacaoAtual = Alocacao::where('professor_id', $professorId)
                ->where('data', now()->toDateString())
                ->where('horario_inicio', '<=', $horarioAtual)
                ->where(function($query) use ($horarioAtual) {
                    $query->whereNull('horario_fim')
                          ->orWhere('horario_fim', '>=', $horarioAtual);
                })
                ->with('sala')
                ->first();

            return response()->json([
                'professor' => $professor,
                'esta_alocado' => !is_null($alocacaoAtual),
                'alocacao_atual' => $alocacaoAtual
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao verificar professor: ' . $e->getMessage()
            ], 500);
        }
    }
}
