import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
  }
});

export const directorService = {
  getProfessores: () => api.get('/professores'),
  getSalas: () => api.get('/salas'),
  getAlocacoesAtuais: () => api.get('/alocacoes/atuais'),
  alocarProfessor: (data) => api.post('/alocacoes', data),
  desalocarProfessor: (alocacaoId) => api.delete(`/alocacoes/${alocacaoId}`),
  verificarProfessor: (professorId) => api.get(`/professores/${professorId}/verificar-disponibilidade`),
};

export default api;