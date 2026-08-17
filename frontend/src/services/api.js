const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

const request = async (path, options = {}) => {
  const headers = {
    ...(options.headers || {}),
  };

  // Só definir Content-Type quando houver corpo JSON (POST/PUT/PATCH)
  const method = (options.method || 'GET').toUpperCase();
  if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method) && options.body) {
    headers['Content-Type'] = 'application/json';
  }

  const response = await fetch(`${API_BASE_URL}${path}`, {
    headers,
    ...options,
  });

  const text = await response.text();
  let data = null;
  if (text) {
    try {
      data = JSON.parse(text);
    } catch (e) {
      // resposta não é JSON (HTML de erro ou texto simples) — manter raw para diagnóstico
      data = { _raw: text };
    }
  }

  if (!response.ok) {
    const error = new Error((data && data.message) || 'Erro na requisicao');
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
  criarProfessor: (data) => request('/professores', {
    method: 'POST',
    body: JSON.stringify(data),
  }),
  editarProfessor: (id, data) => request(`/professores/${id}`, {
    method: 'PUT',
    body: JSON.stringify(data),
  }),
  excluirProfessor: (id) => request(`/professores/${id}`, {
    method: 'DELETE',
  }),
  getDocentes: () => request('/docentes'),
  getDisciplinas: () => request('/disciplinas'),
  vincularDocenteDisciplina: (data) => request('/docentes/vincular-disciplina', {
    method: 'POST',
    body: JSON.stringify(data),
  }),
  desvincularDocenteDisciplina: (docenteId) => request(`/docentes/${docenteId}/disciplina`, {
    method: 'DELETE',
  }),
  getSalas: () => request('/salas'),

  getHorariosAulas: () => request('/horarios-aulas'),
  getHorariosAulasPorSala: (salaId) => request(`/salas/${salaId}/horarios-aulas`),
  getAlocacoesAtuais: () => request('/alocacoes/atuais'),
  getAlocacoesSemana: () => request('/alocacoes/semana'),
  getAlocacoesAluno: (professorId, disciplinaId) => request(
    `/alocacoes/consulta-aluno?professor_id=${encodeURIComponent(professorId)}&disciplina_id=${encodeURIComponent(disciplinaId)}`
  ),
  alocarProfessor: (data) => request('/alocacoes', {
    method: 'POST',
    body: JSON.stringify(data),
  }),
  desalocarProfessor: (alocacaoId) => request(`/alocacoes/${alocacaoId}`, {
    method: 'DELETE',
  }),
  exportarAlocacoesXlsx: async () => {
    const response = await fetch(`${API_BASE_URL}/alocacoes/exportar-xlsx`);
    if (!response.ok) {
      throw new Error('Erro ao exportar XLSX');
    }
    return response.blob();
  },
  exportarProfessoresLecionandoXlsx: async (professorId) => {
    const params = new URLSearchParams();
    if (professorId) {
      params.set('professor_id', professorId);
    }

    const query = params.toString() ? `?${params.toString()}` : '';
    const response = await fetch(`${API_BASE_URL}/alocacoes/professores-lecionando/exportar-xlsx${query}`);
    if (!response.ok) {
      throw new Error('Erro ao exportar XLSX');
    }
    return response.blob();
  },
  verificarProfessor: (professorId) => request(`/professores/${professorId}/verificar-disponibilidade`),
};

export default request;
