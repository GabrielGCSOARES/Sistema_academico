const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

const request = async (path, options = {}) => {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    headers: {
      'Content-Type': 'application/json',
      ...(options.headers || {}),
    },
    ...options,
  });

  const text = await response.text();
  const data = text ? JSON.parse(text) : null;

  if (!response.ok) {
    const error = new Error(data?.message || 'Erro na requisicao');
    error.response = {
      status: response.status,
      data,
    };
    throw error;
  }

  return { data };
};

export const directorService = {
  getProfessores: () => request('/professores'),
  getDocentes: () => request('/docentes'),
  getDisciplinas: () => request('/disciplinas'),
  vincularDocenteDisciplina: (data) => request('/docentes/vincular-disciplina', {
    method: 'POST',
    body: JSON.stringify(data),
  }),
  getSalas: () => request('/salas'),

  getHorariosAulas: () => request('/horarios-aulas'),
  getHorariosAulasPorSala: (salaId) => request(`/salas/${salaId}/horarios-aulas`),
  getAlocacoesAtuais: () => request('/alocacoes/atuais'),
  getAlocacoesSemana: () => request('/alocacoes/semana'),
  alocarProfessor: (data) => request('/alocacoes', {
    method: 'POST',
    body: JSON.stringify(data),
  }),
  desalocarProfessor: (alocacaoId) => request(`/alocacoes/${alocacaoId}`, {
    method: 'DELETE',
  }),
  verificarProfessor: (professorId) => request(`/professores/${professorId}/verificar-disponibilidade`),
};

export default request;
