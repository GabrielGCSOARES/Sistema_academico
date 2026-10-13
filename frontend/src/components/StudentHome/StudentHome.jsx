import React, { useEffect, useMemo, useState, useCallback } from 'react';
import { directorService } from '../../services/api';
import CursoSelecaoModal from './CursoSelecaoModal';
import './StudentHome.css';

const DIAS_SEMANA_NOMES = [
  'Segunda-feira',
  'Terça-feira',
  'Quarta-feira',
  'Quinta-feira',
  'Sexta-feira',
  'Sábado',
];

const StudentHome = ({ onBack }) => {
  const [cursos, setCursos] = useState([]);
  const [docentes, setDocentes] = useState([]);
  const [disciplinas, setDisciplinas] = useState([]);

  // Estado do Modal de Seleção de Curso (Padrão: GTI - Matutino - 1º Semestre)
  const [showCursoModal, setShowCursoModal] = useState(false);
  const [cursoInfo, setCursoInfo] = useState({
    cursoId: 3,
    nome: 'Gestão da Tecnologia da Informação',
    codigo: 'GTI',
    turno: 'Manhã',
    periodoNumero: 1,
    periodoTipo: 'Semestre',
    nomeCompleto: 'Gestão da Tecnologia da Informação - 1º Semestre (Manhã)',
  });

  // Filtros por semestre/professor/disciplina
  const [semestreFiltro, setSemestreFiltro] = useState('');
  const [professorId, setProfessorId] = useState('');
  const [disciplinaId, setDisciplinaId] = useState('');

  // Alocações carregadas
  const [alocacoesSemana, setAlocacoesSemana] = useState([]);
  const [carregandoFiltros, setCarregandoFiltros] = useState(true);
  const [carregandoResultados, setCarregandoResultados] = useState(false);
  const [erroFiltros, setErroFiltros] = useState('');
  const [erroResultados, setErroResultados] = useState('');

  const carregarFiltros = useCallback(async () => {
    try {
      setCarregandoFiltros(true);
      const cursoIdAtual = cursoInfo?.cursoId || 3;
      const [docentesResponse, disciplinasResponse, cursosResponse] = await Promise.all([
        directorService.getDocentes(),
        directorService.getDisciplinas({ curso_id: cursoIdAtual }),
        directorService.getCursos ? directorService.getCursos() : Promise.resolve({ data: [] }),
      ]);

      setDocentes(docentesResponse?.data || []);
      setDisciplinas(disciplinasResponse?.data || []);
      setCursos(cursosResponse?.data || []);
      setErroFiltros('');
    } catch (error) {
      setErroFiltros('Não foi possível carregar os dados. Tente novamente.');
      console.error('Erro ao carregar dados da consulta do aluno:', error);
    } finally {
      setCarregandoFiltros(false);
    }
  }, [cursoInfo?.cursoId]);

  useEffect(() => {
    carregarFiltros();
  }, [carregarFiltros]);

  // Carregar alocações da semana com base no curso, turno, semestre ou professor/disciplina
  const carregarGradeSemanal = useCallback(async () => {
    setCarregandoResultados(true);
    setErroResultados('');

    try {
      const params = {};
      if (cursoInfo?.cursoId) {
        params.curso_id = cursoInfo.cursoId;
      }
      if (cursoInfo?.turno) {
        params.turno = cursoInfo.turno;
      }

      const periodoAlvo = semestreFiltro || cursoInfo?.periodoNumero;
      if (periodoAlvo && periodoAlvo !== 'todos') {
        params.periodo_numero = Number(periodoAlvo);
      }

      if (professorId) {
        params.professor_id = professorId;
      }
      if (disciplinaId) {
        params.disciplina_id = disciplinaId;
      }

      // Calcula a data da Segunda-feira da semana atual
      const agora = new Date();
      const diaDaSemana = agora.getDay(); // 0 Dom, 1 Seg, ..., 6 Sab
      const diffParaSegunda = diaDaSemana === 0 ? -6 : 1 - diaDaSemana;
      const segunda = new Date(agora);
      segunda.setDate(agora.getDate() + diffParaSegunda);
      const ano = segunda.getFullYear();
      const mes = String(segunda.getMonth() + 1).padStart(2, '0');
      const dia = String(segunda.getDate()).padStart(2, '0');
      params.inicio = `${ano}-${mes}-${dia}`;

      const response = await directorService.getAlocacoesAluno(params);
      setAlocacoesSemana(response?.data || []);
    } catch (error) {
      setAlocacoesSemana([]);
      setErroResultados('Não foi possível consultar os horários. Tente novamente.');
      console.error('Erro ao consultar grade semanal do aluno:', error);
    } finally {
      setCarregandoResultados(false);
    }
  }, [cursoInfo, semestreFiltro, professorId, disciplinaId]);

  useEffect(() => {
    if (cursoInfo || (professorId && disciplinaId)) {
      carregarGradeSemanal();
    }
  }, [cursoInfo, semestreFiltro, professorId, disciplinaId, carregarGradeSemanal]);

  const professorSelecionado = useMemo(
    () => docentes.find((docente) => Number(docente.id) === Number(professorId)),
    [docentes, professorId]
  );

  const disciplinasDisponiveis = useMemo(() => {
    let lista = disciplinas;

    if (professorSelecionado?.disciplina_id) {
      lista = lista.filter(
        (disciplina) => Number(disciplina.id) === Number(professorSelecionado.disciplina_id)
      );
    }

    const periodoFiltroAtivo = semestreFiltro || (cursoInfo?.periodoNumero && cursoInfo.periodoNumero !== 'todos' ? cursoInfo.periodoNumero : null);
    if (periodoFiltroAtivo) {
      lista = lista.filter(
        (disciplina) => !disciplina.periodo_numero || Number(disciplina.periodo_numero) === Number(periodoFiltroAtivo)
      );
    }

    return lista;
  }, [disciplinas, professorSelecionado, semestreFiltro, cursoInfo?.periodoNumero]);

  const formatarData = (data) => {
    const [ano, mes, dia] = String(data || '').slice(0, 10).split('-');
    if (!ano || !mes || !dia) return 'Data não informada';
    return `${dia}/${mes}/${ano}`;
  };

  const getDiaSemanaIndice = (dataStr) => {
    if (!dataStr) return -1;
    const [ano, mes, dia] = String(dataStr).slice(0, 10).split('-');
    if (!ano || !mes || !dia) return -1;
    const dt = new Date(Number(ano), Number(mes) - 1, Number(dia));
    const day = dt.getDay(); // 0 Dom, 1 Seg, 2 Ter, 3 Qua, 4 Qui, 5 Sex, 6 Sab
    return day >= 1 && day <= 6 ? day - 1 : -1;
  };

  const formatarHorario = (horario) => String(horario || '').slice(0, 5) || '--:--';

  // Agrupar alocações por dia da semana (0 = Segunda, ..., 5 = Sábado)
  const alocacoesPorDia = useMemo(() => {
    const dias = [[], [], [], [], [], []];
    alocacoesSemana.forEach((aloc) => {
      const idx = getDiaSemanaIndice(aloc.data);
      if (idx >= 0 && idx <= 5) {
        dias[idx].push(aloc);
      }
    });

    // Ordenar horários de cada dia cronologicamente
    dias.forEach((lista) => {
      lista.sort((a, b) => {
        const hA = String(a.horario_inicio || '');
        const hB = String(b.horario_inicio || '');
        return hA.localeCompare(hB);
      });
    });

    return dias;
  }, [alocacoesSemana]);

  const hasFiltrosAtivos = Boolean(professorId || disciplinaId || semestreFiltro);

  return (
    <div className="student-home">
      <header className="student-header">
        <div className="student-header-content">
          <div>
            <p className="student-eyebrow">Fatec Tatuí</p>
            <h1>Consulta de Horários e Salas</h1>
            <p className="student-subtitle">
              Consulte a grade semanal completa de aulas por Curso, Turno e Semestre, ou filtre por Professor e Disciplina.
            </p>
          </div>
          <button type="button" className="back-button" onClick={onBack}>
            ← Voltar ao painel do Diretor
          </button>
        </div>
      </header>

      <main className="student-content">
        {/* Card Principal de Seleção de Curso */}
        <section className="student-card course-selection-card">
          <div className="course-banner-inner">
            <div className="course-banner-text">
              <span className="course-badge-pill">Grade Curricular</span>
              {cursoInfo ? (
                <div>
                  <h2 className="selected-course-title">{cursoInfo.nome}</h2>
                  <span className="selected-course-meta">
                    {cursoInfo.periodoNumero ? `${cursoInfo.periodoNumero}º ${cursoInfo.periodoTipo} — ` : 'Todos os Semestres — '}
                    Turno {cursoInfo.turno} {cursoInfo.codigo ? `(${cursoInfo.codigo} - ${cursoInfo.turno === 'Manhã' ? 'Matutino' : cursoInfo.turno})` : ''}
                  </span>
                </div>
              ) : (
                <div>
                  <h2 className="selected-course-title">Nenhum curso selecionado</h2>
                  <span className="selected-course-meta">
                    Selecione seu curso e semestre para visualizar a grade semanal com salas e horários.
                  </span>
                </div>
              )}
            </div>

            <button
              type="button"
              className="btn-select-course-main"
              onClick={() => setShowCursoModal(true)}
            >
              {cursoInfo ? '🔄 Alterar Curso / Turno' : ' Selecionar Meu Curso'}
            </button>
          </div>
        </section>

        {/* Card de Filtros Opcionais de Semestre, Professor e Disciplina */}
        <section className="student-card filters-card" aria-labelledby="filters-title">
          <div className="section-heading">
            <div>
              <p className="section-kicker">Filtros Opcionais</p>
              <h2 id="filters-title">Buscar por Semestre, Professor ou Disciplina</h2>
            </div>
            {hasFiltrosAtivos && (
              <button
                type="button"
                className="btn-clear-filters"
                onClick={() => {
                  setProfessorId('');
                  setDisciplinaId('');
                  setSemestreFiltro('');
                }}
              >
                Limpar Filtros
              </button>
            )}
          </div>

          {erroFiltros ? (
            <div className="student-message student-message--error" role="alert">
              <p>{erroFiltros}</p>
              <button type="button" onClick={carregarFiltros}>
                Recarregar filtros
              </button>
            </div>
          ) : (
            <div className="filters-grid">
              {/* Botão de Curso */}
              <label className="filter-field">
                <span>Curso Selecionado</span>
                <button
                  type="button"
                  className="course-filter-trigger"
                  onClick={() => setShowCursoModal(true)}
                >
                  {cursoInfo ? cursoInfo.nomeCompleto : 'Clique para escolher Curso e Semestre'}
                </button>
              </label>

              {/* Semestre */}
              <label className="filter-field">
                <span>Semestre</span>
                <select
                  value={semestreFiltro}
                  onChange={(event) => {
                    setSemestreFiltro(event.target.value);
                    setDisciplinaId('');
                  }}
                  disabled={carregandoFiltros}
                >
                  <option value="">Todos os semestres</option>
                  {[1, 2, 3, 4, 5, 6].map((num) => (
                    <option key={num} value={num}>
                      {num}º Semestre
                    </option>
                  ))}
                </select>
              </label>

              {/* Professor */}
              <label className="filter-field">
                <span>Professor</span>
                <select
                  value={professorId}
                  onChange={(event) => {
                    setProfessorId(event.target.value);
                  }}
                  disabled={carregandoFiltros}
                >
                  <option value="">Todos os professores</option>
                  {docentes.map((docente) => (
                    <option key={docente.id} value={docente.id}>
                      {docente.nome}
                    </option>
                  ))}
                </select>
              </label>

              {/* Disciplina */}
              <label className="filter-field">
                <span>Disciplina</span>
                <select
                  value={disciplinaId}
                  onChange={(event) => setDisciplinaId(event.target.value)}
                  disabled={carregandoFiltros || Boolean(professorSelecionado && !professorSelecionado.disciplina_id)}
                >
                  <option value="">
                    {professorSelecionado && !professorSelecionado.disciplina_id
                      ? 'Professor sem disciplina vinculada'
                      : 'Todas as disciplinas'}
                  </option>
                  {disciplinasDisponiveis.map((disciplina) => {
                    const siglaStr = disciplina.sigla ? `${disciplina.sigla} - ` : '';
                    const semStr = disciplina.periodo_numero ? ` (${disciplina.periodo_numero}º Sem)` : '';
                    return (
                      <option key={disciplina.id} value={disciplina.id}>
                        {`${siglaStr}${disciplina.nome}${semStr}`}
                      </option>
                    );
                  })}
                </select>
              </label>
            </div>
          )}
        </section>

        {/* Grade Horária da Semana */}
        <section className="student-card results-card" aria-live="polite" aria-labelledby="results-title">
          <div className="section-heading">
            <div>
              <p className="section-kicker">Grade Horária da Semana</p>
              <h2 id="results-title">
                {cursoInfo
                  ? `Aulas da Semana — ${cursoInfo.nome} (${cursoInfo.turno})`
                  : 'Horários e Salas das Aulas'}
              </h2>
            </div>
            {alocacoesSemana.length > 0 && !carregandoResultados && (
              <span className="result-count">
                {alocacoesSemana.length} {alocacoesSemana.length === 1 ? 'aula programada' : 'aulas programadas'}
              </span>
            )}
          </div>

          {!cursoInfo && !professorId && !disciplinaId && (
            <div className="student-empty-state">
              <span className="empty-icon" aria-hidden="true"></span>
              <p>Clique no botão acima para selecionar seu Curso, Turno e Semestre, ou consulte por Professor.</p>
              <button
                type="button"
                className="btn-select-course-empty"
                onClick={() => setShowCursoModal(true)}
              >
                Selecionar Meu Curso Agora
              </button>
            </div>
          )}

          {carregandoResultados && (
            <div className="student-empty-state">
              <span className="student-spinner" aria-label="Carregando horários" />
              <p>Carregando horários e salas da grade...</p>
            </div>
          )}

          {!carregandoResultados && erroResultados && (
            <div className="student-message student-message--error" role="alert">
              <p>{erroResultados}</p>
            </div>
          )}

          {!carregandoResultados && !erroResultados && (cursoInfo || (professorId && disciplinaId)) && alocacoesSemana.length === 0 && (
            <div className="student-empty-state">
              <span className="empty-icon" aria-hidden="true">◷</span>
              <p>
                Não foram encontradas aulas cadastradas para este período/filtro na semana.
              </p>
            </div>
          )}

          {/* Grade Semanal por Dias da Semana (Segunda a Sábado) */}
          {!carregandoResultados && !erroResultados && alocacoesSemana.length > 0 && (
            <div className="weekly-schedule-grid">
              {DIAS_SEMANA_NOMES.map((nomeDia, idx) => {
                const aulasDoDia = alocacoesPorDia[idx] || [];

                return (
                  <div key={nomeDia} className="schedule-day-column">
                    <div className="day-header">
                      <h3>{nomeDia}</h3>
                      <span className="day-count-badge">
                        {aulasDoDia.length} {aulasDoDia.length === 1 ? 'aula' : 'aulas'}
                      </span>
                    </div>

                    <div className="day-classes-list">
                      {aulasDoDia.length === 0 ? (
                        <div className="empty-day-box">
                          <span>Sem aulas agendadas</span>
                        </div>
                      ) : (
                        aulasDoDia.map((aloc) => {
                          const profNome = aloc.professor?.nome || 'Professor não informado';
                          const discObj = aloc.professor?.docente?.disciplina;
                          const sigla = discObj?.sigla || '';
                          const materia = discObj?.nome || aloc.professor?.disciplina || 'Disciplina';
                          const semestreNum = discObj?.periodo_numero;
                          const salaNome = aloc.sala?.nome || 'Sala não informada';
                          const predioNome = aloc.predio || aloc.sala?.predio || 'Prédio 1';

                          return (
                            <article key={aloc.id} className="class-schedule-card">
                              <div className="class-card-top-row">
                                <span className="class-time-badge">
                                  {formatarHorario(aloc.horario_inicio)} às {formatarHorario(aloc.horario_fim)}
                                </span>
                                {semestreNum && (
                                  <span className="class-semester-tag">
                                    {semestreNum}º Sem.
                                  </span>
                                )}
                              </div>

                              {aloc.data && (
                                <div className="class-date-tag">
                                  {formatarData(aloc.data)}
                                </div>
                              )}

                              <h4 className="class-subject-title">
                                {`${sigla ? `${sigla} - ` : ''}${materia}`}
                              </h4>

                              <p className="class-professor-name">
                                <strong>Professor:</strong> {profNome}
                              </p>

                              <div className="class-location-footer">
                                <span className="room-tag">
                                  <strong>Sala:</strong> {salaNome}
                                </span>
                                <span className="building-tag">{predioNome}</span>
                              </div>
                            </article>
                          );
                        })
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </section>
      </main>

      {/* Modal de Seleção de Curso */}
      {showCursoModal && (
        <CursoSelecaoModal
          cursosDisponiveis={cursos}
          cursoAtual={cursoInfo}
          onClose={() => setShowCursoModal(false)}
          onConfirmar={(dados) => {
            setCursoInfo(dados);
            setSemestreFiltro('');
            setDisciplinaId('');
          }}
        />
      )}
    </div>
  );
};

export default StudentHome;
