import React from 'react';
import { render, screen, waitFor } from '@testing-library/react';
import StudentHome from './StudentHome';
import { directorService } from '../../services/api';

jest.mock('../../services/api', () => ({
  directorService: {
    getDocentes: jest.fn(),
    getDisciplinas: jest.fn(),
    getCursos: jest.fn(),
    getAlocacoesAluno: jest.fn(),
  },
}));

describe('StudentHome Component', () => {
  const mockDocentes = [
    { id: 4, nome: 'ANDRÉ LUIZ FORMIGONI', disciplina_id: 6 },
    { id: 7, nome: 'ANDRÉA PAVAN PERIN', disciplina_id: 8 },
  ];

  const mockDisciplinas = [
    { id: 6, sigla: 'IAL004', nome: 'Algoritmos', periodo_numero: 1 },
    { id: 8, sigla: 'LIN100', nome: 'Inglês I', periodo_numero: 1 },
    { id: 10, sigla: 'ILP100', nome: 'Linguagens de Programação', periodo_numero: 2 },
  ];

  const mockAlocacoes = [
    {
      id: 1,
      data: '2026-09-07', // Segunda-feira
      horario_inicio: '07:40:00',
      horario_fim: '09:20:00',
      predio: 'Prédio 1',
      sala: { id: 1, nome: 'Sala 01', predio: 'Prédio 1' },
      professor: {
        id: 4,
        nome: 'ANDRÉ LUIZ FORMIGONI',
        disciplina: 'Algoritmos',
        docente: {
          id: 4,
          disciplina: { id: 6, sigla: 'IAL004', nome: 'Algoritmos', periodo_numero: 1 },
        },
      },
    },
    {
      id: 2,
      data: '2026-09-12', // Sábado
      horario_inicio: '08:00:00',
      horario_fim: '11:40:00',
      predio: 'Prédio 2',
      sala: { id: 6, nome: 'Auditório', predio: 'Prédio 2' },
      professor: {
        id: 10,
        nome: 'ARNALDO GONÇALVES',
        disciplina: 'Atividades Acadêmico-científico-culturais I',
        docente: {
          id: 10,
          disciplina: { id: 7, sigla: 'TAA001', nome: 'Atividades Acadêmico-científico-culturais I', periodo_numero: 1 },
        },
      },
    },
  ];

  beforeEach(() => {
    jest.clearAllMocks();
    directorService.getDocentes.mockResolvedValue({ data: mockDocentes });
    directorService.getDisciplinas.mockResolvedValue({ data: mockDisciplinas });
    directorService.getCursos.mockResolvedValue({
      data: [{ id: 3, nome: 'Gestão da Tecnologia da Informação', codigo: 'GTI', turnos: ['Manhã', 'Noite'] }],
    });
    directorService.getAlocacoesAluno.mockResolvedValue({ data: mockAlocacoes });
  });

  test('renderiza a tela com o curso GTI - Matutino e os 6 dias da semana', async () => {
    render(<StudentHome onBack={jest.fn()} />);

    // Verifica título do curso padrão
    expect(await screen.findByText(/Aulas da Semana — Gestão da Tecnologia da Informação/i)).toBeInTheDocument();

    // Verifica os 6 dias da semana (Segunda a Sábado)
    expect(await screen.findByRole('heading', { name: 'Segunda-feira' })).toBeInTheDocument();
    expect(screen.getByRole('heading', { name: 'Terça-feira' })).toBeInTheDocument();
    expect(screen.getByRole('heading', { name: 'Quarta-feira' })).toBeInTheDocument();
    expect(screen.getByRole('heading', { name: 'Quinta-feira' })).toBeInTheDocument();
    expect(screen.getByRole('heading', { name: 'Sexta-feira' })).toBeInTheDocument();
    expect(screen.getByRole('heading', { name: 'Sábado' })).toBeInTheDocument();
  });

  test('exibe as aulas cadastradas com sigla, horário, professor e sala', async () => {
    render(<StudentHome onBack={jest.fn()} />);

    // Verifica aula de segunda-feira com sigla e nome
    expect(await screen.findByRole('heading', { name: /IAL004 - Algoritmos/i })).toBeInTheDocument();
    expect(screen.getAllByText(/ANDRÉ LUIZ FORMIGONI/i).length).toBeGreaterThanOrEqual(1);
    expect(screen.getByText(/Sala 01/i)).toBeInTheDocument();

    // Verifica aula de sábado
    expect(await screen.findByRole('heading', { name: /TAA001 - Atividades Acadêmico-científico-culturais I/i })).toBeInTheDocument();
    expect(screen.getByText(/Auditório/i)).toBeInTheDocument();
  });

  test('exibe disciplinas formatadas com sigla no select de filtros', async () => {
    render(<StudentHome onBack={jest.fn()} />);

    await waitFor(() => {
      const options = screen.getAllByRole('option');
      const optionTexts = options.map((opt) => opt.textContent);
      expect(optionTexts.some((txt) => txt.includes('IAL004 - Algoritmos'))).toBe(true);
      expect(optionTexts.some((txt) => txt.includes('LIN100 - Inglês I'))).toBe(true);
    });
  });
});
