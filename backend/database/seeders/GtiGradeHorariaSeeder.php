<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Curso;
use App\Models\Disciplina;
use App\Models\Docente;
use App\Models\Professor;
use App\Models\Sala;
use App\Models\Alocacao;
use Carbon\Carbon;

class GtiGradeHorariaSeeder extends Seeder
{
    public function run(): void
    {
        $curso = Curso::where('codigo', 'GTI')
            ->orWhere('nome', 'Gestão da Tecnologia da Informação')
            ->first();

        if (!$curso) {
            $this->command?->error('Curso GTI não encontrado.');
            return;
        }

        // 1. Mapeamento de Professores existentes com Disciplinas de GTI
        $vinculosDocentes = [
            'ANDRÉ LUIZ FORMIGONI' => 'IAL004', // Algoritmos
            'ANDRÉA PAVAN PERIN' => 'LIN100', // Inglês I
            'CARLOS ALEXANDRE SOARES' => 'MAT006', // Matemática Discreta
            'CLAUDIO SERGIO SARTORI' => 'ITI001', // Fundamentos de Tecnologia da Informação
            'DAIANE RONCATO CARDOZO MONTEIRO' => 'LPO001', // Comunicação e Expressão
            'CESARIO DE MORAES LEONEL FERREIRA' => 'AGO006', // Processos Gerenciais
            'ARNALDO GONÇALVES' => 'TAA001', // Atividades Acadêmico-científico-culturais I
            'DANIEL SOARES DE MARQUES' => 'ILP100', // Linguagens de Programação (Sem 2)
            'DAVID NUNES ZANETI DE SOUZA' => 'IBD951', // Banco de Dados e Aplicações (Sem 3)
            'DENIS APARECIDO NOGUEIRA DE DEUS' => 'IES951', // Engenharia de Software e Aplicações (Sem 3)
            'EDSON FERREIRA PORTELA' => 'IIN001', // Inteligência de Negócios (Sem 6)
            'FABIANA DE OLIVEIRA LINS KITEGROSKI' => 'AGR002', // Gestão de Pessoas (Sem 3)
        ];

        $docentesMap = [];

        foreach ($vinculosDocentes as $nomeDocente => $siglaDisciplina) {
            $disciplina = Disciplina::where('sigla', $siglaDisciplina)->first();
            $docente = Docente::where('nome', $nomeDocente)->first();

            if ($docente && $disciplina) {
                $docente->update(['disciplina_id' => $disciplina->id]);

                // Sincroniza tabela professores
                $professor = Professor::firstOrNew(['id' => $docente->id]);
                $professor->forceFill([
                    'nome' => $docente->nome,
                    'email' => $professor->email ?: sprintf('professor%d@fatec.edu', $docente->id),
                    'disciplina' => $disciplina->nome,
                    'curso' => $curso->nome,
                    'ativo' => true,
                ])->save();

                $docentesMap[$siglaDisciplina] = $docente;
            }
        }

        // 2. Mapeamento de Salas existentes
        $salasMap = [];
        $salasDesejadas = ['Sala 01', 'Sala 02', 'Sala 03', 'Sala 04', 'Multi 01', 'Multi 02', 'Auditório'];
        foreach ($salasDesejadas as $nomeSala) {
            $sala = Sala::where('nome', $nomeSala)->first();
            if ($sala) {
                $salasMap[$nomeSala] = $sala;
            }
        }

        // Fallback para primeira sala se alguma específica não for encontrada
        $salaPadrao = Sala::where('ativa', true)->first();

        // 3. Gerar Alocações para a semana corrente (Segunda a Sábado)
        $inicioSemana = now()->startOfWeek(Carbon::MONDAY);

        // Grade curricular semanal matutina para GTI
        $gradeSemanal = [
            // SEGUNDA-FEIRA (dia 0)
            [
                'dia_offset' => 0,
                'sigla' => 'IAL004',
                'inicio' => '07:40:00',
                'fim' => '09:20:00',
                'sala' => 'Sala 01',
                'predio' => 'Prédio 1',
            ],
            [
                'dia_offset' => 0,
                'sigla' => 'AGO006',
                'inicio' => '09:30:00',
                'fim' => '11:10:00',
                'sala' => 'Sala 01',
                'predio' => 'Prédio 1',
            ],
            [
                'dia_offset' => 0,
                'sigla' => 'ILP100', // 2º Semestre
                'inicio' => '07:40:00',
                'fim' => '09:20:00',
                'sala' => 'Multi 02',
                'predio' => 'Prédio 1',
            ],

            // TERÇA-FEIRA (dia 1)
            [
                'dia_offset' => 1,
                'sigla' => 'MAT006',
                'inicio' => '07:40:00',
                'fim' => '09:20:00',
                'sala' => 'Sala 02',
                'predio' => 'Prédio 1',
            ],
            [
                'dia_offset' => 1,
                'sigla' => 'ITI001',
                'inicio' => '09:30:00',
                'fim' => '11:10:00',
                'sala' => 'Multi 01',
                'predio' => 'Prédio 1',
            ],

            // QUARTA-FEIRA (dia 2)
            [
                'dia_offset' => 2,
                'sigla' => 'LPO001',
                'inicio' => '07:40:00',
                'fim' => '09:20:00',
                'sala' => 'Sala 01',
                'predio' => 'Prédio 1',
            ],
            [
                'dia_offset' => 2,
                'sigla' => 'LIN100',
                'inicio' => '09:30:00',
                'fim' => '11:10:00',
                'sala' => 'Sala 03',
                'predio' => 'Prédio 1',
            ],
            [
                'dia_offset' => 2,
                'sigla' => 'IBD951', // 3º Semestre
                'inicio' => '09:30:00',
                'fim' => '11:10:00',
                'sala' => 'Multi 01',
                'predio' => 'Prédio 1',
            ],

            // QUINTA-FEIRA (dia 3)
            [
                'dia_offset' => 3,
                'sigla' => 'IAL004',
                'inicio' => '07:40:00',
                'fim' => '09:20:00',
                'sala' => 'Multi 02',
                'predio' => 'Prédio 1',
            ],
            [
                'dia_offset' => 3,
                'sigla' => 'MAT006',
                'inicio' => '09:30:00',
                'fim' => '11:10:00',
                'sala' => 'Sala 02',
                'predio' => 'Prédio 1',
            ],
            [
                'dia_offset' => 3,
                'sigla' => 'IES951', // 3º Semestre
                'inicio' => '07:40:00',
                'fim' => '09:20:00',
                'sala' => 'Sala 04',
                'predio' => 'Prédio 2',
            ],

            // SEXTA-FEIRA (dia 4)
            [
                'dia_offset' => 4,
                'sigla' => 'ITI001',
                'inicio' => '07:40:00',
                'fim' => '09:20:00',
                'sala' => 'Sala 01',
                'predio' => 'Prédio 1',
            ],
            [
                'dia_offset' => 4,
                'sigla' => 'LPO001',
                'inicio' => '09:30:00',
                'fim' => '11:10:00',
                'sala' => 'Sala 01',
                'predio' => 'Prédio 1',
            ],

            // SÁBADO (dia 5)
            [
                'dia_offset' => 5,
                'sigla' => 'TAA001',
                'inicio' => '08:00:00',
                'fim' => '11:40:00',
                'sala' => 'Auditório',
                'predio' => 'Prédio 2',
            ],
        ];

        $alocacoesCriadas = 0;

        foreach ($gradeSemanal as $aula) {
            $docente = $docentesMap[$aula['sigla']] ?? null;
            if (!$docente) {
                continue;
            }

            $sala = $salasMap[$aula['sala']] ?? $salaPadrao;
            if (!$sala) {
                continue;
            }

            $dataAula = $inicioSemana->copy()->addDays($aula['dia_offset'])->toDateString();
            $predio = $aula['predio'] ?? $sala->predio ?? 'Prédio 1';

            Alocacao::updateOrCreate(
                [
                    'professor_id' => $docente->id,
                    'data' => $dataAula,
                    'horario_inicio' => $aula['inicio'],
                ],
                [
                    'sala_id' => $sala->id,
                    'predio' => $predio,
                    'horario_fim' => $aula['fim'],
                ]
            );

            $alocacoesCriadas++;
        }

        $this->command?->info("GtiGradeHorariaSeeder: {$alocacoesCriadas} alocações da semana registradas com sucesso para GTI - Matutino!");
    }
}
