<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Curso;
use App\Models\CursoPeriodo;
use App\Models\Disciplina;

class EstruturaCurricularSeeder extends Seeder
{
    /**
     * Estrutura curricular completa dos 6 cursos especificados:
     * 1. GTI - N (Gestão da Tecnologia da Informação - Noite)
     * 2. AI - M (Automação Industrial - Manhã)
     * 3. AI - N (Automação Industrial - Noite)
     * 4. MI - M (Manutenção Industrial - Manhã)
     * 5. MI - N (Manutenção Industrial - Noite)
     * 6. PFO - T (Produção Fonográfica - Tarde)
     */
    private const ESTRUTURA_CURRICULAR = [
        // CURSO 1: GTI - N
        [
            'curso_nome' => 'Gestão da Tecnologia da Informação',
            'curso_codigo' => 'GTI',
            'turno' => 'Noite',
            'sigla_curso_turno' => 'GTI - N',
            'semestres' => [
                1 => [
                    ['sigla' => 'AGO006', 'nome' => 'Processos Gerenciais'],
                    ['sigla' => 'IAL004', 'nome' => 'Algoritmos'],
                    ['sigla' => 'ITI001', 'nome' => 'Fundamentos de Tecnologia da Informação'],
                    ['sigla' => 'LIN100', 'nome' => 'Inglês I'],
                    ['sigla' => 'LPO001', 'nome' => 'Comunicação e Expressão'],
                    ['sigla' => 'MAT006', 'nome' => 'Matemática Discreta'],
                    ['sigla' => 'TAA001', 'nome' => 'Atividades Acadêmico-científico-culturais I'],
                ],
                2 => [
                    ['sigla' => 'IGS001', 'nome' => 'Gestão de Sistemas Operacionais'],
                    ['sigla' => 'IHW001', 'nome' => 'Laboratório de Hardware'],
                    ['sigla' => 'ILP100', 'nome' => 'Linguagens de Programação'],
                    ['sigla' => 'IMP951', 'nome' => 'Modelagem de Processos'],
                    ['sigla' => 'LIN200', 'nome' => 'Inglês II'],
                    ['sigla' => 'MMF001', 'nome' => 'Matemática Financeira'],
                    ['sigla' => 'MPT004', 'nome' => 'Metodologia da Pesquisa Científico-tecnológica'],
                    ['sigla' => 'TAA002', 'nome' => 'Atividades Acadêmico-científico-culturais II'],
                    ['sigla' => 'TGI103', 'nome' => 'Atividades de Projetos I (Modelagem de Processos)'],
                ],
                3 => [
                    ['sigla' => 'AGA001', 'nome' => 'Gestão Ambiental'],
                    ['sigla' => 'AGR002', 'nome' => 'Gestão de Pessoas'],
                    ['sigla' => 'CON102', 'nome' => 'Contabilidade'],
                    ['sigla' => 'IBD951', 'nome' => 'Banco de Dados e Aplicações'],
                    ['sigla' => 'IES951', 'nome' => 'Engenharia de Software e Aplicações'],
                    ['sigla' => 'LIN300', 'nome' => 'Inglês III'],
                    ['sigla' => 'MET001', 'nome' => 'Estatística'],
                    ['sigla' => 'TGI203', 'nome' => 'Atividades de Projetos II (Engenharia de Software)'],
                    ['sigla' => 'TGI303', 'nome' => 'Atividades de Projetos III (Banco de Dados)'],
                ],
                4 => [
                    ['sigla' => 'AGF001', 'nome' => 'Gestão Financeira'],
                    ['sigla' => 'AGP001', 'nome' => 'Gestão da Produção'],
                    ['sigla' => 'ILP951', 'nome' => 'Programação para Internet'],
                    ['sigla' => 'IRC108', 'nome' => 'Redes de Computadores'],
                    ['sigla' => 'LIN400', 'nome' => 'Inglês IV'],
                    ['sigla' => 'PMG007', 'nome' => 'Fundamentos de Marketing'],
                    ['sigla' => 'TGI403', 'nome' => 'Atividades de Projetos IV (Programação para Internet)'],
                ],
                5 => [
                    ['sigla' => 'AGE001', 'nome' => 'Planejamento e Gestão Estratégica'],
                    ['sigla' => 'GPJ002', 'nome' => 'Gestão de Projetos'],
                    ['sigla' => 'IGT002', 'nome' => 'Gestão de Tecnologia da Informação'],
                    ['sigla' => 'IPT001', 'nome' => 'Projetos de Tecnologia da Informação I'],
                    ['sigla' => 'ISI951', 'nome' => 'Sistemas Integrados de Gestão e Aplicações'],
                    ['sigla' => 'LIN500', 'nome' => 'Inglês V'],
                    ['sigla' => 'TGI503', 'nome' => 'Atividades de Projetos V (Sistemas Integrados de Gestão)'],
                ],
                6 => [
                    ['sigla' => 'AGE003', 'nome' => 'Gestão Econômica'],
                    ['sigla' => 'DDI001', 'nome' => 'Legislação Aplicada à Tecnologia da Informação'],
                    ['sigla' => 'EGI004', 'nome' => 'Estágio Supervisionado em Gestão da Tecnologia da Informação'],
                    ['sigla' => 'IIN001', 'nome' => 'Inteligência de Negócios'],
                    ['sigla' => 'INE001', 'nome' => 'Negócios Eletrônicos'],
                    ['sigla' => 'IPT951', 'nome' => 'Projetos de Tecnologia da Informação II'],
                    ['sigla' => 'LIN600', 'nome' => 'Inglês VI'],
                    ['sigla' => 'TAI001', 'nome' => 'Tópicos Avançados em Tecnologia da Informação'],
                    ['sigla' => 'TGI012', 'nome' => 'Trabalho de Graduação em Gestão da Tecnologia da Informação'],
                    ['sigla' => 'TGI603', 'nome' => 'Atividades de Projetos VI (Projetos de Tecnologia da Informação II)'],
                ],
            ],
        ],

        // CURSO 2: AI - M
        [
            'curso_nome' => 'Automação Industrial',
            'curso_codigo' => 'AI',
            'turno' => 'Manhã',
            'sigla_curso_turno' => 'AI - M',
            'semestres' => [
                1 => [
                    ['sigla' => 'DET101', 'nome' => 'Introdução ao Desenho Técnico'],
                    ['sigla' => 'EEE103', 'nome' => 'Eletricidade Aplicada à Automação'],
                    ['sigla' => 'FFM002', 'nome' => 'Física (Mecânica Oscilatória)'],
                    ['sigla' => 'IAL100', 'nome' => 'Lógica de Programação Aplicada'],
                    ['sigla' => 'LPO101', 'nome' => 'Português'],
                    ['sigla' => 'MAT002', 'nome' => 'Fundamentos de Matemática Aplicada à Automação'],
                    ['sigla' => 'MCA103', 'nome' => 'Cálculo I'],
                ],
                2 => [
                    ['sigla' => 'DTC013', 'nome' => 'Introdução ao Desenho Assistido por Computador'],
                    ['sigla' => 'EEA205', 'nome' => 'Automação I'],
                    ['sigla' => 'EEA502', 'nome' => 'Eletrônica Analógica I'],
                    ['sigla' => 'EED501', 'nome' => 'Eletrônica Digital I'],
                    ['sigla' => 'FAT002', 'nome' => 'Fenômenos de Transporte'],
                    ['sigla' => 'FEM002', 'nome' => 'Física (Eletricidade e Eletromagnetismo)'],
                    ['sigla' => 'MCA104', 'nome' => 'Cálculo II'],
                ],
                3 => [
                    ['sigla' => 'EEA103', 'nome' => 'Sistemas de Controle'],
                    ['sigla' => 'EEA206', 'nome' => 'Automação II'],
                    ['sigla' => 'EEA503', 'nome' => 'Eletrônica Analógica II'],
                    ['sigla' => 'EED502', 'nome' => 'Eletrônica Digital II'],
                    ['sigla' => 'EEM001', 'nome' => 'Microcontroladores'],
                    ['sigla' => 'EMH005', 'nome' => 'Hidráulica e Pneumática'],
                    ['sigla' => 'MET001', 'nome' => 'Estatística'],
                ],
                4 => [
                    ['sigla' => 'EEA003', 'nome' => 'Sensores e Instrumentação'],
                    ['sigla' => 'EEA200', 'nome' => 'Laboratório de Automação'],
                    ['sigla' => 'EEE100', 'nome' => 'Eletrônica de Potência'],
                    ['sigla' => 'EEE104', 'nome' => 'Controladores Programáveis I'],
                    ['sigla' => 'EEE202', 'nome' => 'Máquinas Elétricas I'],
                    ['sigla' => 'ILP105', 'nome' => 'Programação Aplicada à Automação'],
                ],
                5 => [
                    ['sigla' => 'EEA204', 'nome' => 'Controladores Programáveis II'],
                    ['sigla' => 'EEA207', 'nome' => 'Automação III'],
                    ['sigla' => 'EEE203', 'nome' => 'Máquinas Elétricas II'],
                    ['sigla' => 'EEI102', 'nome' => 'Redes Industriais'],
                    ['sigla' => 'EMR001', 'nome' => 'Robótica Industrial'],
                    ['sigla' => 'EPA003', 'nome' => 'Organização Industrial'],
                    ['sigla' => 'TTG003', 'nome' => 'Trabalho de Graduação I'],
                    ['sigla' => 'TTG301', 'nome' => 'Projeto de Trabalho de Graduação I'],
                ],
                6 => [
                    ['sigla' => 'AGP201', 'nome' => 'Sistema de Gestão Integrado'],
                    ['sigla' => 'CEE001', 'nome' => 'Inovação e Empreendedorismo'],
                    ['sigla' => 'EEA208', 'nome' => 'Automação IV'],
                    ['sigla' => 'EEE200', 'nome' => 'Instalações Elétricas Industriais'],
                    ['sigla' => 'EEI103', 'nome' => 'Sistemas Supervisórios'],
                    ['sigla' => 'EPI002', 'nome' => 'Sistemas Flexíveis da Manufatura'],
                    ['sigla' => 'TES004', 'nome' => 'Prática Profissional'],
                    ['sigla' => 'TTG103', 'nome' => 'Trabalho de Graduação II'],
                    ['sigla' => 'TTG302', 'nome' => 'Projeto de Trabalho de Graduação II'],
                ],
            ],
        ],

        // CURSO 3: AI - N
        [
            'curso_nome' => 'Automação Industrial',
            'curso_codigo' => 'AI',
            'turno' => 'Noite',
            'sigla_curso_turno' => 'AI - N',
            'semestres' => [
                1 => [
                    ['sigla' => 'DET101', 'nome' => 'Introdução ao Desenho Técnico'],
                    ['sigla' => 'EEE103', 'nome' => 'Eletricidade Aplicada à Automação'],
                    ['sigla' => 'FFM002', 'nome' => 'Física (Mecânica Oscilatória)'],
                    ['sigla' => 'IAL100', 'nome' => 'Lógica de Programação Aplicada'],
                    ['sigla' => 'LPO101', 'nome' => 'Português'],
                    ['sigla' => 'MAT002', 'nome' => 'Fundamentos de Matemática Aplicada à Automação'],
                    ['sigla' => 'MCA103', 'nome' => 'Cálculo I'],
                ],
                2 => [
                    ['sigla' => 'DTC013', 'nome' => 'Introdução ao Desenho Assistido por Computador'],
                    ['sigla' => 'EEA205', 'nome' => 'Automação I'],
                    ['sigla' => 'EEA502', 'nome' => 'Eletrônica Analógica I'],
                    ['sigla' => 'EED501', 'nome' => 'Eletrônica Digital I'],
                    ['sigla' => 'FAT002', 'nome' => 'Fenômenos de Transporte'],
                    ['sigla' => 'FEM002', 'nome' => 'Física (Eletricidade e Eletromagnetismo)'],
                    ['sigla' => 'MCA104', 'nome' => 'Cálculo II'],
                ],
                3 => [
                    ['sigla' => 'EEA103', 'nome' => 'Sistemas de Controle'],
                    ['sigla' => 'EEA206', 'nome' => 'Automação II'],
                    ['sigla' => 'EEA503', 'nome' => 'Eletrônica Analógica II'],
                    ['sigla' => 'EED502', 'nome' => 'Eletrônica Digital II'],
                    ['sigla' => 'EEM001', 'nome' => 'Microcontroladores'],
                    ['sigla' => 'EMH005', 'nome' => 'Hidráulica e Pneumática'],
                    ['sigla' => 'MET016', 'nome' => 'Estatística Básica'], // Específico de AI - N
                ],
                4 => [
                    ['sigla' => 'EEA003', 'nome' => 'Sensores e Instrumentação'],
                    ['sigla' => 'EEA200', 'nome' => 'Laboratório de Automação'],
                    ['sigla' => 'EEE100', 'nome' => 'Eletrônica de Potência'],
                    ['sigla' => 'EEE104', 'nome' => 'Controladores Programáveis I'],
                    ['sigla' => 'EEE202', 'nome' => 'Máquinas Elétricas I'],
                    ['sigla' => 'ILP105', 'nome' => 'Programação Aplicada à Automação'],
                ],
                5 => [
                    ['sigla' => 'EEA204', 'nome' => 'Controladores Programáveis II'],
                    ['sigla' => 'EEA207', 'nome' => 'Automação III'],
                    ['sigla' => 'EEE203', 'nome' => 'Máquinas Elétricas II'],
                    ['sigla' => 'EEI102', 'nome' => 'Redes Industriais'],
                    ['sigla' => 'EMR001', 'nome' => 'Robótica Industrial'],
                    ['sigla' => 'EPA003', 'nome' => 'Organização Industrial'],
                    ['sigla' => 'TTG003', 'nome' => 'Trabalho de Graduação I'],
                    ['sigla' => 'TTG301', 'nome' => 'Projeto de Trabalho de Graduação I'],
                ],
                6 => [
                    ['sigla' => 'AGP201', 'nome' => 'Sistema de Gestão Integrado'],
                    ['sigla' => 'CEE017', 'nome' => 'Inovação e Empreendedorismo'], // Específico de AI - N
                    ['sigla' => 'EEA208', 'nome' => 'Automação IV'],
                    ['sigla' => 'EEE200', 'nome' => 'Instalações Elétricas Industriais'],
                    ['sigla' => 'EEI103', 'nome' => 'Sistemas Supervisórios'],
                    ['sigla' => 'EPI002', 'nome' => 'Sistemas Flexíveis da Manufatura'],
                    ['sigla' => 'TES004', 'nome' => 'Prática Profissional'],
                    ['sigla' => 'TTG103', 'nome' => 'Trabalho de Graduação II'],
                    ['sigla' => 'TTG302', 'nome' => 'Projeto de Trabalho de Graduação II'],
                ],
            ],
        ],

        // CURSO 4: MI - M
        [
            'curso_nome' => 'Manutenção Industrial',
            'curso_codigo' => 'MI',
            'turno' => 'Manhã',
            'sigla_curso_turno' => 'MI - M',
            'semestres' => [
                1 => [
                    ['sigla' => 'COM010', 'nome' => 'Fundamentos de Comunicação e Expressão'],
                    ['sigla' => 'DTC020', 'nome' => 'Desenho Auxiliado por Computador'],
                    ['sigla' => 'DTC021', 'nome' => 'Desenho Técnico Mecânico'],
                    ['sigla' => 'EMA047', 'nome' => 'Ciência dos Materiais'],
                    ['sigla' => 'FMT012', 'nome' => 'Metrologia'],
                    ['sigla' => 'IAL005', 'nome' => 'Lógica de Programação'],
                    ['sigla' => 'ING001', 'nome' => 'Inglês I'],
                    ['sigla' => 'MAT009', 'nome' => 'Tópicos de Matemática Elementar'],
                ],
                2 => [
                    ['sigla' => 'EMA133', 'nome' => 'Tratamento Térmico e Tratamento de Superfície'],
                    ['sigla' => 'EMP007', 'nome' => 'Processos de Fabricação I'],
                    ['sigla' => 'FFE004', 'nome' => 'Eletricidade'],
                    ['sigla' => 'FMA010', 'nome' => 'Mecânica Clássica'],
                    ['sigla' => 'ING002', 'nome' => 'Inglês II'],
                    ['sigla' => 'MCA018', 'nome' => 'Cálculo I'],
                    ['sigla' => 'MPT007', 'nome' => 'Metodologia da Pesquisa Científico-tecnológica'],
                ],
                3 => [
                    ['sigla' => 'CCC009', 'nome' => 'Custos Industriais'],
                    ['sigla' => 'EMA028', 'nome' => 'Resistência dos Materiais'],
                    ['sigla' => 'EMA031', 'nome' => 'Máquinas Elétricas'],
                    ['sigla' => 'EMA134', 'nome' => 'Comandos Elétricos'],
                    ['sigla' => 'EMP008', 'nome' => 'Processos de Fabricação II'],
                    ['sigla' => 'MCA019', 'nome' => 'Cálculo II'],
                    ['sigla' => 'MFL001', 'nome' => 'Mecânica dos Fluidos'],
                    ['sigla' => 'SSO003', 'nome' => 'Segurança no Trabalho'],
                ],
                4 => [
                    ['sigla' => 'AGA007', 'nome' => 'Gestão Ambiental'],
                    ['sigla' => 'AGQ012', 'nome' => 'Gestão da Qualidade'],
                    ['sigla' => 'EEA012', 'nome' => 'Eletrônica'],
                    ['sigla' => 'EMA048', 'nome' => 'Instrumentação Industrial'],
                    ['sigla' => 'EME013', 'nome' => 'Elementos de Máquinas'],
                    ['sigla' => 'EMH015', 'nome' => 'Sistemas Hidráulicos e Pneumáticos'],
                    ['sigla' => 'EPG003', 'nome' => 'Planejamento e Controle da Manutenção'],
                    ['sigla' => 'EST010', 'nome' => 'Estatística Descritiva'],
                ],
                5 => [
                    ['sigla' => 'CNC002', 'nome' => 'Tecnologia do CNC'],
                    ['sigla' => 'DDE008', 'nome' => 'Fundamentos de Direito Empresarial'],
                    ['sigla' => 'EMA034', 'nome' => 'Análise de Sistemas Térmicos'],
                    ['sigla' => 'EMA043', 'nome' => 'Gerenciamento da Manutenção'],
                    ['sigla' => 'EMA045', 'nome' => 'Acionamento Industrial - CLP'],
                    ['sigla' => 'EMA046', 'nome' => 'Ensaios não Destrutivos'],
                    ['sigla' => 'EMM007', 'nome' => 'Soldagem'],
                    ['sigla' => 'TMI004', 'nome' => 'Gestão do Trabalho de Graduação'],
                    ['sigla' => 'TMI005', 'nome' => 'Trabalho de Graduação em Manutenção Industrial I'],
                ],
                6 => [
                    ['sigla' => 'AGO028', 'nome' => 'Gestão de Projetos'],
                    ['sigla' => 'EMA035', 'nome' => 'Análise de Falhas'],
                    ['sigla' => 'EMA036', 'nome' => 'Manutenção de Instalações Elétricas'],
                    ['sigla' => 'EMA037', 'nome' => 'Manutenção Centrada em Confiabilidade'],
                    ['sigla' => 'EMA038', 'nome' => 'Manutenção Industrial'],
                    ['sigla' => 'EMA039', 'nome' => 'Manutenção de Máquinas Térmicas'],
                    ['sigla' => 'EMA040', 'nome' => 'Gerenciamento e Conservação de Energia'],
                    ['sigla' => 'EMA135', 'nome' => 'Tópicos Especiais'],
                    ['sigla' => 'EMI005', 'nome' => 'Estágio Supervisionado em Manutenção Industrial'],
                    ['sigla' => 'TMI006', 'nome' => 'Trabalho de Graduação em Manutenção Industrial II'],
                ],
            ],
        ],

        // CURSO 5: MI - N (Idêntico ao MI - M)
        [
            'curso_nome' => 'Manutenção Industrial',
            'curso_codigo' => 'MI',
            'turno' => 'Noite',
            'sigla_curso_turno' => 'MI - N',
            'semestres' => [
                1 => [
                    ['sigla' => 'COM010', 'nome' => 'Fundamentos de Comunicação e Expressão'],
                    ['sigla' => 'DTC020', 'nome' => 'Desenho Auxiliado por Computador'],
                    ['sigla' => 'DTC021', 'nome' => 'Desenho Técnico Mecânico'],
                    ['sigla' => 'EMA047', 'nome' => 'Ciência dos Materiais'],
                    ['sigla' => 'FMT012', 'nome' => 'Metrologia'],
                    ['sigla' => 'IAL005', 'nome' => 'Lógica de Programação'],
                    ['sigla' => 'ING001', 'nome' => 'Inglês I'],
                    ['sigla' => 'MAT009', 'nome' => 'Tópicos de Matemática Elementar'],
                ],
                2 => [
                    ['sigla' => 'EMA133', 'nome' => 'Tratamento Térmico e Tratamento de Superfície'],
                    ['sigla' => 'EMP007', 'nome' => 'Processos de Fabricação I'],
                    ['sigla' => 'FFE004', 'nome' => 'Eletricidade'],
                    ['sigla' => 'FMA010', 'nome' => 'Mecânica Clássica'],
                    ['sigla' => 'ING002', 'nome' => 'Inglês II'],
                    ['sigla' => 'MCA018', 'nome' => 'Cálculo I'],
                    ['sigla' => 'MPT007', 'nome' => 'Metodologia da Pesquisa Científico-tecnológica'],
                ],
                3 => [
                    ['sigla' => 'CCC009', 'nome' => 'Custos Industriais'],
                    ['sigla' => 'EMA028', 'nome' => 'Resistência dos Materiais'],
                    ['sigla' => 'EMA031', 'nome' => 'Máquinas Elétricas'],
                    ['sigla' => 'EMA134', 'nome' => 'Comandos Elétricos'],
                    ['sigla' => 'EMP008', 'nome' => 'Processos de Fabricação II'],
                    ['sigla' => 'MCA019', 'nome' => 'Cálculo II'],
                    ['sigla' => 'MFL001', 'nome' => 'Mecânica dos Fluidos'],
                    ['sigla' => 'SSO003', 'nome' => 'Segurança no Trabalho'],
                ],
                4 => [
                    ['sigla' => 'AGA007', 'nome' => 'Gestão Ambiental'],
                    ['sigla' => 'AGQ012', 'nome' => 'Gestão da Qualidade'],
                    ['sigla' => 'EEA012', 'nome' => 'Eletrônica'],
                    ['sigla' => 'EMA048', 'nome' => 'Instrumentação Industrial'],
                    ['sigla' => 'EME013', 'nome' => 'Elementos de Máquinas'],
                    ['sigla' => 'EMH015', 'nome' => 'Sistemas Hidráulicos e Pneumáticos'],
                    ['sigla' => 'EPG003', 'nome' => 'Planejamento e Controle da Manutenção'],
                    ['sigla' => 'EST010', 'nome' => 'Estatística Descritiva'],
                ],
                5 => [
                    ['sigla' => 'CNC002', 'nome' => 'Tecnologia do CNC'],
                    ['sigla' => 'DDE008', 'nome' => 'Fundamentos de Direito Empresarial'],
                    ['sigla' => 'EMA034', 'nome' => 'Análise de Sistemas Térmicos'],
                    ['sigla' => 'EMA043', 'nome' => 'Gerenciamento da Manutenção'],
                    ['sigla' => 'EMA045', 'nome' => 'Acionamento Industrial - CLP'],
                    ['sigla' => 'EMA046', 'nome' => 'Ensaios não Destrutivos'],
                    ['sigla' => 'EMM007', 'nome' => 'Soldagem'],
                    ['sigla' => 'TMI004', 'nome' => 'Gestão do Trabalho de Graduação'],
                    ['sigla' => 'TMI005', 'nome' => 'Trabalho de Graduação em Manutenção Industrial I'],
                ],
                6 => [
                    ['sigla' => 'AGO028', 'nome' => 'Gestão de Projetos'],
                    ['sigla' => 'EMA035', 'nome' => 'Análise de Falhas'],
                    ['sigla' => 'EMA036', 'nome' => 'Manutenção de Instalações Elétricas'],
                    ['sigla' => 'EMA037', 'nome' => 'Manutenção Centrada em Confiabilidade'],
                    ['sigla' => 'EMA038', 'nome' => 'Manutenção Industrial'],
                    ['sigla' => 'EMA039', 'nome' => 'Manutenção de Máquinas Térmicas'],
                    ['sigla' => 'EMA040', 'nome' => 'Gerenciamento e Conservação de Energia'],
                    ['sigla' => 'EMA135', 'nome' => 'Tópicos Especiais'],
                    ['sigla' => 'EMI005', 'nome' => 'Estágio Supervisionado em Manutenção Industrial'],
                    ['sigla' => 'TMI006', 'nome' => 'Trabalho de Graduação em Manutenção Industrial II'],
                ],
            ],
        ],

        // CURSO 6: PFO - T
        [
            'curso_nome' => 'Produção Fonográfica',
            'curso_codigo' => 'PFO',
            'turno' => 'Tarde',
            'sigla_curso_turno' => 'PFO - T',
            'semestres' => [
                1 => [
                    ['sigla' => 'COM012', 'nome' => 'Fundamentos da Comunicação Empresarial'],
                    ['sigla' => 'FAG001', 'nome' => 'Introdução à Acústica'],
                    ['sigla' => 'FEE001', 'nome' => 'Eletricidade e Eletrônica I'],
                    ['sigla' => 'HST001', 'nome' => 'Sociedade, Tecnologia e Inovação'],
                    ['sigla' => 'INF109', 'nome' => 'Informática para a Produção Fonográfica'],
                    ['sigla' => 'ING013', 'nome' => 'Inglês I'],
                    ['sigla' => 'LMU107', 'nome' => 'Teoria e Percepção Musical I'],
                    ['sigla' => 'LMU108', 'nome' => 'Introdução à Produção Fonográfica'],
                    ['sigla' => 'LMU301', 'nome' => 'Projeto Interdisciplinar em Produção Fonográfica I'],
                    ['sigla' => 'TTG100', 'nome' => 'Métodos para a Produção do Conhecimento'],
                ],
                2 => [
                    ['sigla' => 'ADM001', 'nome' => 'Fundamentos de Administração Geral'],
                    ['sigla' => 'FAG002', 'nome' => 'Acústica Aplicada à Produção Fonográfica'],
                    ['sigla' => 'FEE002', 'nome' => 'Eletricidade e Eletrônica II'],
                    ['sigla' => 'HMU001', 'nome' => 'Noções de História da Música'],
                    ['sigla' => 'IMU001', 'nome' => 'Protocolo Midi'],
                    ['sigla' => 'ING014', 'nome' => 'Inglês II'],
                    ['sigla' => 'LMU102', 'nome' => 'Teoria e Percepção Musical II'],
                    ['sigla' => 'LMU109', 'nome' => 'Introdução à Prática Musical'],
                    ['sigla' => 'LMU203', 'nome' => 'Rítmica I'],
                    ['sigla' => 'LMU302', 'nome' => 'Projeto Interdisciplinar em Produção Fonográfica II'],
                    ['sigla' => 'TAA102', 'nome' => 'Atividades Acadêmico-Científico-Culturais'],
                ],
                3 => [
                    ['sigla' => 'EEG005', 'nome' => 'Técnicas de Gravação I'],
                    ['sigla' => 'FAM001', 'nome' => 'Acústica Aplicada ao Instrumento'],
                    ['sigla' => 'HMU002', 'nome' => 'História da Música Popular'],
                    ['sigla' => 'IMU002', 'nome' => 'Consoles Analógicos e Digitais'],
                    ['sigla' => 'ING015', 'nome' => 'Inglês III'],
                    ['sigla' => 'LMU103', 'nome' => 'Teoria e Percepção Musical III'],
                    ['sigla' => 'LMU202', 'nome' => 'Rítmica II'],
                    ['sigla' => 'LMU303', 'nome' => 'Projeto Interdisciplinar em Produção Fonográfica III'],
                ],
                4 => [
                    ['sigla' => 'DMU001', 'nome' => 'Direito Fonográfico'],
                    ['sigla' => 'EEF001', 'nome' => 'Mixagem I'],
                    ['sigla' => 'EEG006', 'nome' => 'Técnicas de Gravação II'],
                    ['sigla' => 'EEI001', 'nome' => 'Software e Hardware I'],
                    ['sigla' => 'FAA003', 'nome' => 'Acústica Aplicada ao Ambiente'],
                    ['sigla' => 'ING016', 'nome' => 'Inglês IV'],
                    ['sigla' => 'LMU104', 'nome' => 'Teoria e Percepção Musical IV'],
                    ['sigla' => 'LMU304', 'nome' => 'Projeto Interdisciplinar em Produção Fonográfica IV'],
                    ['sigla' => 'PMG001', 'nome' => 'Fundamentos de Marketing'],
                ],
                5 => [
                    ['sigla' => 'AGR001', 'nome' => 'Fundamentos de Gestão de Pessoas'],
                    ['sigla' => 'EEF002', 'nome' => 'Mixagem II'],
                    ['sigla' => 'EEF005', 'nome' => 'Masterização e Finalização I'],
                    ['sigla' => 'EEG003', 'nome' => 'Técnicas de Gravação III'],
                    ['sigla' => 'EEI002', 'nome' => 'Software e Hardware II'],
                    ['sigla' => 'ING017', 'nome' => 'Inglês V'],
                    ['sigla' => 'LMU105', 'nome' => 'Teoria e Percepção Musical V'],
                    ['sigla' => 'LMU305', 'nome' => 'Projeto Interdisciplinar em Produção Fonográfica V'],
                    ['sigla' => 'NEP003', 'nome' => 'Produção de Eventos'],
                    ['sigla' => 'TPF101', 'nome' => 'Projeto de Trabalho de Graduação em Produção Fonográfica I'],
                    ['sigla' => 'TPF201', 'nome' => 'Trabalho de Graduação em Produção Fonográfica I'],
                ],
                6 => [
                    ['sigla' => 'AGN005', 'nome' => 'Gestão e Negócios em Produção Fonográfica'],
                    ['sigla' => 'EEF004', 'nome' => 'Masterização e Finalização II'],
                    ['sigla' => 'EEG004', 'nome' => 'Técnicas de Gravação IV'],
                    ['sigla' => 'EEI004', 'nome' => 'Software e Hardware III'],
                    ['sigla' => 'EPF002', 'nome' => 'Práticas Profissionais'],
                    ['sigla' => 'ING018', 'nome' => 'Inglês VI'],
                    ['sigla' => 'LMU106', 'nome' => 'Teoria e Percepção Musical VI'],
                    ['sigla' => 'LMU306', 'nome' => 'Projeto Interdisciplinar em Produção Fonográfica VI'],
                    ['sigla' => 'PAF002', 'nome' => 'Marketing Aplicado à Produção Fonográfica'],
                    ['sigla' => 'TPF012', 'nome' => 'Tópicos Avançados em Fonografia'],
                    ['sigla' => 'TPF102', 'nome' => 'Projeto de Trabalho de Graduação em Produção Fonográfica II'],
                    ['sigla' => 'TPF202', 'nome' => 'Trabalho de Graduação em Produção Fonográfica II'],
                ],
            ],
        ],
    ];

    public function run(): void
    {
        $totalCursos = count(self::ESTRUTURA_CURRICULAR);
        $totalSemestres = 0;
        $totalVinculos = 0;

        foreach (self::ESTRUTURA_CURRICULAR as $cursoConfig) {
            $curso = Curso::where('codigo', $cursoConfig['curso_codigo'])
                ->orWhere('nome', $cursoConfig['curso_nome'])
                ->first();

            if (!$curso) {
                $curso = Curso::create([
                    'nome' => $cursoConfig['curso_nome'],
                    'codigo' => $cursoConfig['curso_codigo'],
                    'tipo_periodo' => 'semestral',
                    'total_periodos' => 6,
                    'turnos' => [$cursoConfig['turno']],
                    'ativo' => true,
                ]);
            }

            // Garante que o turno está nos turnos do curso
            $turnos = $curso->turnos ?? [];
            if (!in_array($cursoConfig['turno'], $turnos)) {
                $turnos[] = $cursoConfig['turno'];
                $curso->turnos = $turnos;
                $curso->save();
            }

            foreach ($cursoConfig['semestres'] as $semestreNumero => $disciplinasList) {
                $periodoTipo = 'Semestre';
                $nomeCompleto = "{$curso->nome} - {$semestreNumero}º {$periodoTipo} ({$cursoConfig['turno']})";

                $periodo = CursoPeriodo::updateOrCreate(
                    [
                        'curso_id' => $curso->id,
                        'turno' => $cursoConfig['turno'],
                        'periodo_numero' => $semestreNumero,
                    ],
                    [
                        'periodo_tipo' => $periodoTipo,
                        'nome_completo' => $nomeCompleto,
                        'sigla_curso_turno' => $cursoConfig['sigla_curso_turno'],
                    ]
                );
                $totalSemestres++;

                $disciplinasIds = [];

                foreach ($disciplinasList as $item) {
                    $sigla = strtoupper(trim($item['sigla']));
                    $nome = trim($item['nome']);

                    // 1. Localizar por sigla exata
                    $disciplina = Disciplina::where('sigla', $sigla)->first();

                    // 2. Se não encontrar por sigla, busca por nome canônico apenas onde sigla for nula/vazia
                    // Isso evita que disciplinas com mesmo nome mas siglas distintas (ex: CEE001 vs CEE017) sobrescrevam uma à outra
                    if (!$disciplina) {
                        $upperNome = mb_strtoupper(trim($nome));
                        $disciplina = Disciplina::where(function ($q) {
                                $q->whereNull('sigla')->orWhere('sigla', '');
                            })
                            ->where(function ($q) use ($nome, $upperNome) {
                                $q->where('nome', $nome)->orWhere('nome', $upperNome);
                            })
                            ->first();

                        if (!$disciplina) {
                            $disciplina = Disciplina::where(function ($q) {
                                    $q->whereNull('sigla')->orWhere('sigla', '');
                                })
                                ->get()
                                ->first(function ($d) use ($upperNome) {
                                    return mb_strtoupper(trim($d->nome)) === $upperNome;
                                });
                        }
                    }

                    if ($disciplina) {
                        // Preserva o ID existente e atualiza sigla e nome corretos
                        $disciplina->update([
                            'sigla' => $sigla,
                            'nome' => $nome,
                        ]);
                    } else {
                        $disciplina = Disciplina::create([
                            'sigla' => $sigla,
                            'nome' => $nome,
                            'curso_id' => $curso->id,
                            'curso_periodo_id' => $periodo->id,
                            'turno' => $cursoConfig['turno'],
                            'periodo_numero' => $semestreNumero,
                        ]);
                    }

                    // Se a disciplina ainda não tem curso_id ou periodo_numero preenchidos, preenche como fallback
                    if (!$disciplina->curso_id) {
                        $disciplina->update([
                            'curso_id' => $curso->id,
                            'curso_periodo_id' => $periodo->id,
                            'turno' => $cursoConfig['turno'],
                            'periodo_numero' => $semestreNumero,
                        ]);
                    }

                    $disciplinasIds[] = $disciplina->id;
                }

                // Sincroniza tabela de relacionamento curso_periodo_disciplina de forma exata e idempotente
                $periodo->disciplinas()->sync($disciplinasIds);
                $totalVinculos += count($disciplinasIds);
            }
        }

        $this->command?->info("EstruturaCurricularSeeder concluído com sucesso: {$totalCursos} cursos, {$totalSemestres} semestres e {$totalVinculos} associações curriculares sincronizadas!");
    }
}
