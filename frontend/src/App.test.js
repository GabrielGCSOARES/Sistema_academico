import { fireEvent, render, screen } from '@testing-library/react';
import App from './App';

jest.mock('./services/api', () => ({
  directorService: {
    getProfessores: jest.fn(() => Promise.resolve({ data: [] })),
    getSalas: jest.fn(() => Promise.resolve({ data: [] })),
    getAlocacoesAtuais: jest.fn(() => Promise.resolve({ data: [] })),
    getAlocacoesSemana: jest.fn(() => Promise.resolve({ data: [] })),
    getDisciplinas: jest.fn(() => Promise.resolve({ data: [] })),
    getDocentes: jest.fn(() => Promise.resolve({ data: [] })),
    getAlocacoesAluno: jest.fn(() => Promise.resolve({ data: [] })),
  },
}));

test('navega do painel do diretor para a consulta do aluno', async () => {
  render(<App />);

  expect(await screen.findByRole('heading', { name: /sistema de alocação/i })).toBeInTheDocument();

  fireEvent.click(screen.getByRole('button', { name: /ir para tela aluno/i }));

  expect(await screen.findByRole('heading', { name: /consulta de aulas/i })).toBeInTheDocument();
});
