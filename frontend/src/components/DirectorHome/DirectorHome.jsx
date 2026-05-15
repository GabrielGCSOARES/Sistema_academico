import React, { useState, useEffect } from 'react';
import ProfessorCard from './ProfessorCard';
import SalaCard from './SalaCard';
import AlocacaoModal from './AlocacaoModal';
import { directorService } from '../../services/api';
import './DirectorHome.css';

const DirectorHome = () => {
  const [professores, setProfessores] = useState([]);
  const [salas, setSalas] = useState([]);
  const [alocacoes, setAlocacoes] = useState([]);
  const [selectedProfessor, setSelectedProfessor] = useState(null);
  const [showModal, setShowModal] = useState(false);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);
      const [profResponse, salasResponse, alocResponse] = await Promise.all([
        directorService.getProfessores(),
        directorService.getSalas(),
        directorService.getAlocacoesAtuais()
      ]);
      
      setProfessores(profResponse.data);
      setSalas(salasResponse.data);
      setAlocacoes(alocResponse.data);
      setError(null);
    } catch (err) {
      setError('Erro ao carregar dados. Verifique a conexão com o servidor.');
      console.error('Erro ao carregar dados:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleProfessorSelect = (professor) => {
    setSelectedProfessor(professor);
    setShowModal(true);
  };

  const handleAlocacao = async (salaId) => {
    try {
      await directorService.alocarProfessor({
        professor_id: selectedProfessor.id,
        sala_id: salaId,
        data: new Date().toISOString().split('T')[0],
        horario_atual: new Date().toLocaleTimeString()
      });
      
      await loadData();
      setShowModal(false);
      setSelectedProfessor(null);
      
      alert('Professor alocado com sucesso!');
    } catch (error) {
      if (error.response && error.response.status === 409) {
        alert('ATENÇÃO: Este professor já está lecionando em outra sala neste horário!');
      } else if (error.response && error.response.status === 423) {
        alert('ATENÇÃO: Esta sala já está ocupada neste horário!');
      } else {
        alert('Erro ao alocar professor. Tente novamente.');
      }
      console.error('Erro na alocação:', error);
    }
  };

  const handleDesalocar = async (alocacaoId) => {
    if (window.confirm('Tem certeza que deseja remover esta alocação?')) {
      try {
        await directorService.desalocarProfessor(alocacaoId);
        await loadData();
        alert('Professor desalocado com sucesso!');
      } catch (error) {
        alert('Erro ao desalocar professor.');
        console.error('Erro ao desalocar:', error);
      }
    }
  };

  const isProfessorAlocado = (professorId) => {
    return alocacoes.some(aloc => aloc.professor_id === professorId);
  };

  const getSalaStatus = (salaId) => {
    const alocacao = alocacoes.find(aloc => aloc.sala_id === salaId);
    if (alocacao) {
      const professor = professores.find(p => p.id === alocacao.professor_id);
      return {
        ocupada: true,
        professor: professor?.nome || 'Ocupada',
        alocacaoId: alocacao.id
      };
    }
    return { ocupada: false };
  };

  if (loading) {
    return (
      <div className="loading-container">
        <div className="loading-spinner"></div>
        <p>Carregando dados...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div className="error-container">
        <h2>Erro</h2>
        <p>{error}</p>
        <button onClick={loadData} className="retry-button">
          Tentar novamente
        </button>
      </div>
    );
  }

  return (
    <div className="director-home">
      <header className="header">
        <h1>Sistema de Alocação - Diretor Acadêmico</h1>
        <div className="date-info">
          {new Date().toLocaleDateString('pt-BR', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
          })}
        </div>
      </header>

      <main className="main-content">
        <aside className="professors-sidebar">
          <h2>Professores</h2>
          <div className="professors-list">
            {professores.map(professor => (
              <ProfessorCard
                key={professor.id}
                professor={professor}
                isAlocado={isProfessorAlocado(professor.id)}
                onSelect={() => !isProfessorAlocado(professor.id) && handleProfessorSelect(professor)}
              />
            ))}
          </div>
        </aside>

        <section className="rooms-section">
          <h2>Salas de Aula</h2>
          <div className="rooms-grid">
            {salas.map(sala => {
              const status = getSalaStatus(sala.id);
              return (
                <SalaCard
                  key={sala.id}
                  sala={sala}
                  status={status}
                  onDesalocar={() => handleDesalocar(status.alocacaoId)}
                />
              );
            })}
          </div>
        </section>
      </main>

      {showModal && selectedProfessor && (
        <AlocacaoModal
          professor={selectedProfessor}
          salas={salas}
          alocacoes={alocacoes}
          onClose={() => {
            setShowModal(false);
            setSelectedProfessor(null);
          }}
          onAlocar={handleAlocacao}
        />
      )}
    </div>
  );
};

export default DirectorHome;