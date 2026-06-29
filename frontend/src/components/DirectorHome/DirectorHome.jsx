import React, { useState, useEffect } from 'react';
import ProfessorCard from './ProfessorCard';
import SalaCard from './SalaCard';
import AlocacaoModal from './AlocacaoModal';
import SalaHorarioModal from './SalaHorarioModal';
import DocenteDisciplinaModal from './DocenteDisciplinaModal';
import { directorService } from '../../services/api';
import './DirectorHome.css';

const DirectorHome = () => {
  const [professores, setProfessores] = useState([]);
  const [salas, setSalas] = useState([]);
  const [alocacoes, setAlocacoes] = useState([]);
  const [alocacoesSemana, setAlocacoesSemana] = useState([]);

  const [selectedProfessor, setSelectedProfessor] = useState(null);
  const [selectedDocenteId, setSelectedDocenteId] = useState(null);

  const [selectedSalaDetails, setSelectedSalaDetails] = useState(null);

  const [showModal, setShowModal] = useState(false);
  const [showVinculoModal, setShowVinculoModal] = useState(false);

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);

      const [profResponse, salasResponse, alocResponse, alocSemanaResponse] = await Promise.all([
        directorService.getProfessores(),
        directorService.getSalas(),
        directorService.getAlocacoesAtuais(),
        directorService.getAlocacoesSemana()
      ]);

      setProfessores(profResponse?.data || []);
      setSalas(salasResponse?.data || []);
      setAlocacoes(alocResponse?.data || []);
      setAlocacoesSemana(alocSemanaResponse?.data || []);
      setError(null);

      const atualizarProfessor = (docenteId, disciplinaNome) => {

    setProfessores((lista) =>

        lista.map((professor) => {

            if (
                String(professor.id) === `docente-${docenteId}` ||
                Number(professor.id) === Number(docenteId)
            ) {

                return {

                    ...professor,

                    disciplina: disciplinaNome

                };

            }

            return professor;

        })

    );

};
    } catch (err) {
      setError('Erro ao carregar dados. Verifique a conexão com o servidor.');
      console.error('Erro ao carregar dados:', err);
    } finally {
      setLoading(false);
    }
  };

  const professoresOrdenados = [...professores].sort((a,b)=>{

    const aTem = Boolean(a.disciplina);

    const bTem = Boolean(b.disciplina);

    if(aTem===bTem)
        return a.nome.localeCompare(b.nome);

    return aTem?-1:1;

});

  const handleProfessorSelect = (professor) => {
    setSelectedProfessor(professor);
    setShowModal(true);
  };

  const openVinculoModalParaDocente = (docenteId) => {
    setSelectedDocenteId(docenteId);
    setShowVinculoModal(true);
  };

  const handleSalaDetailsOpen = (sala, predio) => {
    setSelectedSalaDetails({ sala, predio });
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
        alert('Este professor já está lecionando em outra sala neste horário.');
      } else if (error.response && error.response.status === 423) {
        alert('Esta sala já está ocupada neste horário.');
      } else {
        alert('Erro ao alocar professor. Tente novamente.');
      }
      console.error('Erro na alocação:', error);
    }
  };

  const handleHorarioAlocacao = async ({ professorId, salaId, predio, data, horarioInicio, horarioFim }) => {
    try {
      await directorService.alocarProfessor({
        professor_id: professorId,
        sala_id: salaId,
        predio,
        data,
        horario_inicio: horarioInicio,
        horario_fim: horarioFim,
        horario_atual: horarioInicio
      });

      await loadData();
    } catch (error) {
      if (error.response && error.response.status === 409) {
        alert('Este professor já está lecionando em outro horário conflitante.');
      } else if (error.response && error.response.status === 423) {
        alert('Esta sala já está ocupada neste horário.');
      } else {
        alert('Erro ao alocar professor neste horário. Tente novamente.');
      }
      console.error('Erro na alocação por horário:', error);
    }
  };

  const handleDesalocar = async (alocacaoId) => {
    if (window.confirm('Tem certeza que deseja remover esta alocação?')) {
      try {
        await directorService.desalocarProfessor(alocacaoId);
        await loadData();
        alert('Professor retirado com sucesso!');
      } catch (error) {
        alert('Erro ao retirar professor.');
        console.error('Erro ao retirar professor:', error);
      }
    }
  };

  const isProfessorAlocado = (professorId) => {
    return alocacoes.some((aloc) => aloc.professor_id === professorId);
  };

  const getSalaStatus = (salaId, predio) => {
    const alocacao = alocacoes.find(
      (aloc) =>
        Number(aloc.sala_id) === Number(salaId) && (!aloc.predio || aloc.predio === predio)
    );

    if (alocacao) {
      const professor = professores.find((p) => p.id === alocacao.professor_id);
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
            {professoresOrdenados.map(...) => (
              <ProfessorCard
                key={professor.id}
                professor={professor}
                isAlocado={isProfessorAlocado(professor.id)}
                onSelect={() => {

                if (isProfessorAlocado(professor.id))
                    return;

                if (String(professor.id).startsWith("docente-")) {

                    const docenteId = Number(

                        String(professor.id)

                            .replace("docente-","")

                    );

                    openVinculoModalParaDocente(

                        docenteId

                    );

                    return;

                }

                handleProfessorSelect(professor);

            }}
              />
            ))}
          </div>
        </aside>

        <section className="rooms-section">
          <h2>Prédio 1</h2>
          <div className="rooms-grid">
            {salas.map((sala) => {
              const status = getSalaStatus(sala.id, 'Prédio 1');
              return (
                <SalaCard
                  key={sala.id}
                  sala={sala}
                  status={status}
                  onOpenDetails={() => handleSalaDetailsOpen(sala, 'Prédio 1')}
                  onDesalocar={() => handleDesalocar(status.alocacaoId)}
                />
              );
            })}
          </div>
        </section>

        <section className="rooms-section">
          <h2>Prédio 2</h2>
          <div className="rooms-grid">
            {salas.map((sala) => {
              const status = getSalaStatus(sala.id, 'Prédio 2');
              return (
                <SalaCard
                  key={`predio2-${sala.id}`}
                  sala={sala}
                  status={status}
                  onOpenDetails={() => handleSalaDetailsOpen(sala, 'Prédio 2')}
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

      {showVinculoModal && (
        <DocenteDisciplinaModal
          onClose={() => {
            setShowVinculoModal(false);
            setSelectedDocenteId(null);
          }}
          onSaved={async (dados) => {
              atualizarProfessor(

                  dados.docente_id,

                  dados.disciplina_nome

              );

              await loadData();

          }}
          selectedDocenteId={selectedDocenteId}
        />
      )}

      {selectedSalaDetails && (
        <SalaHorarioModal
          sala={selectedSalaDetails.sala}
          predio={selectedSalaDetails.predio}
          professores={professores}
          alocacoes={alocacoesSemana}
          onAlocar={handleHorarioAlocacao}
          onDesalocar={handleDesalocar}
          onClose={() => setSelectedSalaDetails(null)}
        />
      )}
    </div>
  );
};

export default DirectorHome;
