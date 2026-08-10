import React, { useState, useEffect } from 'react';
import ProfessorCard from './ProfessorCard';
import SalaCard from './SalaCard';
import SalaHorarioModal from './SalaHorarioModal';
import AlocacaoModal from './AlocacaoModal';
import ExportModal from './ExportModal';
import { directorService } from '../../services/api';
import './DirectorHome.css';

const DirectorHome = () => {
  const [professores, setProfessores] = useState([]);
  const [salas, setSalas] = useState([]);
  const [alocacoes, setAlocacoes] = useState([]);
  const [alocacoesSemana, setAlocacoesSemana] = useState([]);

  const [selectedDocenteId, setSelectedDocenteId] = useState(null);
  const [selectedSalaDetails, setSelectedSalaDetails] = useState(null);

  const [showVinculoModal, setShowVinculoModal] = useState(false);
  const [showRelatorio, setShowRelatorio] = useState(false);
  const [relatorioProfessorId, setRelatorioProfessorId] = useState('');
  const [pendingAlocacao, setPendingAlocacao] = useState(null);
  const [showExportModal, setShowExportModal] = useState(false);

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async (showLoading = true) => {
  try {
    if (showLoading) {
      setLoading(true);
    }

    const [profResponse, salasResponse, alocResponse, alocSemanaResponse, discResponse, docResponse] = await Promise.all([
      directorService.getProfessores(),
      directorService.getSalas(),
      directorService.getAlocacoesAtuais(),
      directorService.getAlocacoesSemana(),
      directorService.getDisciplinas(),
      directorService.getDocentes(),
    ]);

    setProfessores(profResponse?.data || []);
    setSalas(salasResponse?.data || []);
    setAlocacoes(alocResponse?.data || []);
    setAlocacoesSemana(alocSemanaResponse?.data || []);
    setDisciplinas(discResponse?.data || []);
    setDocentes(docResponse?.data || []);
    setError(null);
  } catch (err) {
    setError('Erro ao carregar dados. Verifique a conexão com o servidor.');
    console.error('Erro ao carregar dados:', err);
  } finally {
    if (showLoading) {
      setLoading(false);
      }
    }
  };

  const atualizarProfessor = (docenteId, disciplinaNome) => {
    setProfessores((lista) =>
      lista.map((professor) => {
        if (
          String(professor.id) === `docente-${docenteId}` ||
          Number(professor.id) === Number(docenteId)
        ) {
          return {
            ...professor,
            disciplina: disciplinaNome,
          };
        }
        return professor;
      })
    );
  };

  const professoresOrdenados = [...professores].sort((a, b) => {
    const aTem = Boolean(a.disciplina && a.disciplina !== 'Sem disciplina');
    const bTem = Boolean(b.disciplina && b.disciplina !== 'Sem disciplina');
    if (aTem === bTem) return a.nome.localeCompare(b.nome);
    return aTem ? -1 : 1;
  });

  const formatDateBR = (date) => {
    const [year, month, day] = String(date || '').slice(0, 10).split('-');
    if (!year || !month || !day) return '-';
    return `${day}/${month}/${year}`;
  };

  const normalizeTime = (time) => String(time || '').slice(0, 5);

  const alocacoesRelatorio = alocacoesSemana
    .filter((alocacao) => (
      !relatorioProfessorId || Number(alocacao.professor_id) === Number(relatorioProfessorId)
    ))
    .sort((a, b) => {
      const dataA = `${String(a.data || '').slice(0, 10)} ${normalizeTime(a.horario_inicio)}`;
      const dataB = `${String(b.data || '').slice(0, 10)} ${normalizeTime(b.horario_inicio)}`;
      return dataA.localeCompare(dataB);
    });

  const closeVinculoModal = () => {
    setShowVinculoModal(false);
    setSelectedDocenteId(null);
    setPendingAlocacao(null);
  };

  const openVinculoModalParaDocente = (docenteId) => {
    const numericId = String(docenteId).startsWith('docente-')
      ? Number(String(docenteId).replace('docente-', ''))
      : Number(docenteId);
    setSelectedDocenteId(numericId);
    setShowVinculoModal(true);
  };

  const handleSalaDetailsOpen = (sala, predio) => {
    setSelectedSalaDetails({ sala, predio });
  };

  const handleHorarioSelecionado = async ({ professorId, salaId, predio, data, horarioInicio, horarioFim }) => {
    const docenteId = String(professorId).startsWith('docente-')
      ? Number(String(professorId).replace('docente-', ''))
      : Number(professorId);

    const professor = professores.find(
      (p) => Number(p.id) === docenteId || String(p.id) === `docente-${docenteId}`
    );

    const temDisciplina =
      professor &&
      professor.disciplina &&
      professor.disciplina !== 'Sem disciplina' &&
      professor.disciplina !== 'Disciplina não informada';

    if (!temDisciplina) {
      setPendingAlocacao({ professorId: docenteId, salaId, predio, data, horarioInicio, horarioFim });
      setSelectedDocenteId(docenteId);
      setShowVinculoModal(true);
    } else {
      await handleHorarioAlocacao({
        professorId: docenteId,
        salaId,
        predio,
        data,
        horarioInicio,
        horarioFim
      });
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
        await loadData(false);
      } catch (error) {
        alert('Erro ao retirar professor.');
        console.error('Erro ao retirar professor:', error);
      }
    }
  };

  // ── Redefinir (remover) disciplina do professor ──
  const handleResetDisciplina = async (professorId) => {
    const docenteId = String(professorId).startsWith('docente-')
      ? Number(String(professorId).replace('docente-', ''))
      : Number(professorId);

    try {
      await directorService.desvincularDocenteDisciplina(docenteId);
      atualizarProfessor(docenteId, 'Sem disciplina');
      await loadData(false);
    } catch (err) {
      const msg =
        err?.response?.data?.message ||
        'Não foi possível remover a disciplina do professor.';
      alert(msg);
      throw err;
    }
  };

  const handleExportarRelatorioXlsx = async () => {
    try {
      const blob = await directorService.exportarProfessoresLecionandoXlsx(relatorioProfessorId);
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = 'professores-lecionando.xlsx';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
    } catch (error) {
      alert('Erro ao exportar o relatório para XLSX.');
      console.error('Erro ao exportar XLSX:', error);
    }
  };

  const getSalaStatus = (salaId, predio) => {
    const alocacao =
      alocacoesSemana.find(
        (aloc) => Number(aloc.sala_id) === Number(salaId) && (!aloc.predio || aloc.predio === predio)
      ) ||
      alocacoes.find(
        (aloc) => Number(aloc.sala_id) === Number(salaId) && (!aloc.predio || aloc.predio === predio)
      );

    if (alocacao) {
      const professor = professores.find((p) => Number(p.id) === Number(alocacao.professor_id));
      return {
        ocupada: true,
        professor: alocacao.professor?.nome || professor?.nome || 'Ocupada',
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
        <h1>Sistema de Alocação - Fatec Tatuí</h1>
        <div className="header-actions">
          <button
            type="button"
            className={`export-button ${showRelatorio ? 'active' : ''}`}
            onClick={() => setShowRelatorio((value) => !value)}
          >
            Professores lecionando
          </button>
          <button type="button" className="export-button export-button--highlight" onClick={() => setShowExportModal(true)}>
            ⬇ Exportar XLSX
          </button>
          <div className="date-info">
            {new Date().toLocaleDateString('pt-BR', {
              weekday: 'long',
              year: 'numeric',
              month: 'long',
              day: 'numeric'
            })}
          </div>
        </div>
      </header>

      {showRelatorio && (
        <section className="teaching-report">
          <div className="teaching-report-header">
            <div>
              <h2>Professores lecionando</h2>
              <span>{alocacoesRelatorio.length} alocações encontradas</span>
            </div>

            <label className="professor-filter">
              <span>Professor</span>
              <select
                value={relatorioProfessorId}
                onChange={(event) => setRelatorioProfessorId(event.target.value)}
              >
                <option value="">Todos os professores</option>
                {professoresOrdenados.map((professor) => (
                  <option key={professor.id} value={professor.id}>
                    {professor.nome}
                  </option>
                ))}
              </select>
            </label>
          </div>

          <div className="teaching-report-table-wrap">
            <table className="teaching-report-table">
              <thead>
                <tr>
                  <th>Professor</th>
                  <th>Dia</th>
                  <th>Sala</th>
                  <th>Prédio</th>
                  <th>Horário</th>
                  <th>Matéria</th>
                </tr>
              </thead>
              <tbody>
                {alocacoesRelatorio.length === 0 ? (
                  <tr>
                    <td colSpan="6" className="empty-report">
                      Nenhum professor lecionando neste filtro.
                    </td>
                  </tr>
                ) : (
                  alocacoesRelatorio.map((alocacao) => (
                    <tr key={alocacao.id}>
                      <td>{alocacao.professor?.nome || 'Sem professor'}</td>
                      <td>{formatDateBR(alocacao.data)}</td>
                      <td>{alocacao.sala?.nome || 'Sem sala'}</td>
                      <td>{alocacao.predio || 'Sem prédio'}</td>
                      <td>
                        {normalizeTime(alocacao.horario_inicio)}
                        {' às '}
                        {normalizeTime(alocacao.horario_fim) || '-'}
                      </td>
                      <td>{alocacao.professor?.disciplina || 'Sem disciplina'}</td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </section>
      )}

      <main className="main-content">
        <aside className="professors-sidebar">
          <h2>Professores (Atribuir Disciplina)</h2>
          
          <div className="professors-list">
            {professoresOrdenados.map((professor) => (
              <ProfessorCard
                key={professor.id}
                professor={professor}
                onOpenModal={openVinculoModalParaDocente}
                onResetDisciplina={handleResetDisciplina}
              />
            ))}
          </div>
        </aside>

        <section className="rooms-section">
          <h2>Prédio 1</h2>
          <div className="rooms-grid">
            {salas.filter((sala) => !sala.predio || sala.predio === 'Prédio 1').map((sala) => {
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
            {salas.filter((sala) => sala.predio === 'Prédio 2').map((sala) => {
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

      {showVinculoModal && (
        <AlocacaoModal
          onClose={closeVinculoModal}
          onSaved={async (dados) => {
            atualizarProfessor(dados.docente_id, dados.disciplina_nome);

            if (pendingAlocacao) {
              const aloc = pendingAlocacao;
              closeVinculoModal();
              await handleHorarioAlocacao({
                ...aloc,
                professorId: Number(dados.docente_id),
              });
              return;
            }
          
            await loadData(false);
            closeVinculoModal();
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
          onHorarioSelecionado={handleHorarioSelecionado}
          onDesalocar={handleDesalocar}
          onOpenVinculoModal={openVinculoModalParaDocente}
          onClose={() => setSelectedSalaDetails(null)}
        />
      )}

      {showExportModal && (
        <ExportModal
          onClose={() => setShowExportModal(false)}
          onExportProfessor={handleExportarRelatorioXlsx}
          professores={professoresOrdenados}
          relatorioProfessorId={relatorioProfessorId}
          setRelatorioProfessorId={setRelatorioProfessorId}
        />
      )}
    </div>
  );
};

export default DirectorHome;
