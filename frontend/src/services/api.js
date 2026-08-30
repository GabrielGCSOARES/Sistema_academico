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
  // Cursos e Estruturas
  getCursos: () => request('/cursos'),
  getCurso: (id) => request(`/cursos/${id}`),
  getCursoPeriodos: (params = {}) => {
    const query = new URLSearchParams(params).toString();
    return request(`/curso-periodos${query ? `?${query}` : ''}`);
  },
  criarCurso: (data) => request('/cursos', {
    method: 'POST',
    body: JSON.stringify(data),
  }),

  // Professores
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

  // Disciplinas & Siglas
  getDisciplinas: (params = {}) => {
    const query = new URLSearchParams(params).toString();
    return request(`/disciplinas${query ? `?${query}` : ''}`);
  },
  criarDisciplina: (data) => request('/disciplinas', {
    method: 'POST',
    body: JSON.stringify(data),
  }),
  atualizarSiglaDisciplina: (disciplinaId, sigla, sigla_secundaria = null) => request(`/disciplinas/${disciplinaId}/sigla`, {
    method: 'PUT',
    body: JSON.stringify({ sigla, sigla_secundaria }),
  }),
  redefinirSiglasDisciplina: (disciplinaId) => request(`/disciplinas/${disciplinaId}/redefinir-siglas`, {
    method: 'PUT',
  }),
  atualizarDisciplina: (disciplinaId, data) => request(`/disciplinas/${disciplinaId}`, {
    method: 'PUT',
    body: JSON.stringify(data),
  }),
  excluirDisciplina: (disciplinaId) => request(`/disciplinas/${disciplinaId}`, {
    method: 'DELETE',
  }),

  // Docentes & Vínculos
  getDocentes: () => request('/docentes'),
  vincularDocenteDisciplina: (data) => request('/docentes/vincular-disciplina', {
    method: 'POST',
    body: JSON.stringify(data),
  }),
  desvincularDocenteDisciplina: (docenteId) => request(`/docentes/${docenteId}/disciplina`, {
    method: 'DELETE',
  }),

  // Salas & Horários
  getSalas: () => request('/salas'),
  getHorariosAulas: () => request('/horarios-aulas'),
  getHorariosAulasPorSala: (salaId) => request(`/salas/${salaId}/horarios-aulas`),
  getAlocacoesAtuais: () => request('/alocacoes/atuais'),
  getAlocacoesSemana: () => request('/alocacoes/semana'),
  getAlocacoesAluno: (paramsOrProfessorId, disciplinaId = null) => {
    if (typeof paramsOrProfessorId === 'object' && paramsOrProfessorId !== null) {
      const query = new URLSearchParams(
        Object.entries(paramsOrProfessorId).filter(([_, v]) => v !== undefined && v !== null && v !== '')
      ).toString();
      return request(`/alocacoes/consulta-aluno?${query}`);
    }
    const params = {};
    if (paramsOrProfessorId) params.professor_id = paramsOrProfessorId;
    if (disciplinaId) params.disciplina_id = disciplinaId;
    const query = new URLSearchParams(params).toString();
    return request(`/alocacoes/consulta-aluno?${query}`);
  },
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
