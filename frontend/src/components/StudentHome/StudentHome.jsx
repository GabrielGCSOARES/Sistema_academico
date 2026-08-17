import React, { useEffect, useMemo, useState } from 'react';
import { directorService } from '../../services/api';
import './StudentHome.css';

const initialFilters = {
  curso: '',
  professorId: '',
  salaId: '',
  disciplinaId: '',
};

const StudentHome = ({ onBack }) => {
  const [docentes, setDocentes] = useState([]);
  const [disciplinas, setDisciplinas] = useState([]);
  const [filtros, setFiltros] = useState(initialFilters);
  const [resultados, setResultados] = useState([]);
  const [carregandoFiltros, setCarregandoFiltros] = useState(true);
  const [carregandoResultados, setCarregandoResultados] = useState(false);
  const [erroFiltros, setErroFiltros] = useState('');
  const [erroResultados, setErroResultados] = useState('');

  useEffect(() => {
    let ativo = true;

    const carregarFiltros = async () => {
      try {
        const [docentesResponse, disciplinasResponse] = await Promise.all([
          directorService.getDocentes(),
          directorService.getDisciplinas(),
        ]);

        if (!ativo) return;

        setDocentes(docentesResponse?.data || []);
        setDisciplinas(disciplinasResponse?.data || []);
        setErroFiltros('');
      } catch (error) {
        if (ativo) {
          setErroFiltros('Não foi possível carregar os filtros. Tente novamente.');
        }
        console.error('Erro ao carregar filtros da consulta do aluno:', error);
      } finally {
        if (ativo) {
          setCarregandoFiltros(false);
        }
      }
    };

    carregarFiltros();

    return () => {
      ativo = false;
    };
  }, []);

  const professorSelecionado = useMemo(
    () => docentes.find((docente) => Number(docente.id) === Number(filtros.professorId)),
    [docentes, filtros.professorId]
  );

  const disciplinasDisponiveis = useMemo(() => {
    if (!professorSelecionado?.disciplina_id) {
      return professorSelecionado ? [] : disciplinas;
    }

    return disciplinas.filter(
      (disciplina) => Number(disciplina.id) === Number(professorSelecionado.disciplina_id)
    );
  }, [disciplinas, professorSelecionado]);

  const professoresDisponiveis = useMemo(() => {
    if (!filtros.disciplinaId) {
      return docentes;
    }

    return docentes.filter(
      (docente) => Number(docente.disciplina_id) === Number(filtros.disciplinaId)
    );
  }, [docentes, filtros.disciplinaId]);

  const consultaPronta = Boolean(filtros.professorId && filtros.disciplinaId);

  useEffect(() => {
    let ativo = true;

    if (!consultaPronta) {
      setResultados([]);
      setErroResultados('');
      setCarregandoResultados(false);
      return () => {
        ativo = false;
      };
    }

    const carregarResultados = async () => {
      setCarregandoResultados(true);
      setErroResultados('');

      try {
        const response = await directorService.getAlocacoesAluno(
          filtros.professorId,
          filtros.disciplinaId
        );

        if (ativo) {
          setResultados(response?.data || []);
        }
      } catch (error) {
        if (ativo) {
          setResultados([]);
          setErroResultados('Não foi possível consultar os horários. Tente novamente.');
        }
        console.error('Erro ao consultar horários para o aluno:', error);
      } finally {
        if (ativo) {
          setCarregandoResultados(false);
        }
      }
    };

    carregarResultados();

    return () => {
      ativo = false;
    };
  }, [consultaPronta, filtros.disciplinaId, filtros.professorId]);

  const atualizarProfessor = (professorId) => {
    const proximoProfessor = docentes.find((docente) => Number(docente.id) === Number(professorId));
    const disciplinaAindaValida =
      proximoProfessor?.disciplina_id &&
      Number(proximoProfessor.disciplina_id) === Number(filtros.disciplinaId);

    setFiltros((filtrosAtuais) => ({
      ...filtrosAtuais,
      professorId,
      disciplinaId: disciplinaAindaValida ? filtrosAtuais.disciplinaId : '',
    }));
  };

  const atualizarDisciplina = (disciplinaId) => {
    const professorAindaValido =
      professorSelecionado?.disciplina_id &&
      Number(professorSelecionado.disciplina_id) === Number(disciplinaId);

    setFiltros((filtrosAtuais) => ({
      ...filtrosAtuais,
      disciplinaId,
      professorId: professorAindaValido ? filtrosAtuais.professorId : '',
    }));
  };

  const formatarData = (data) => {
    const [ano, mes, dia] = String(data || '').slice(0, 10).split('-');

    if (!ano || !mes || !dia) return 'Data não informada';

    return new Intl.DateTimeFormat('pt-BR', {
      weekday: 'long',
      day: '2-digit',
      month: 'long',
      year: 'numeric',
    }).format(new Date(Number(ano), Number(mes) - 1, Number(dia)));
  };

  const formatarHorario = (horario) => String(horario || '').slice(0, 5) || '--:--';

  const disciplinaSelecionada = disciplinas.find(
    (disciplina) => Number(disciplina.id) === Number(filtros.disciplinaId)
  );

  return (
    <div className="student-home">
      <header className="student-header">
        <div className="student-header-content">
          <div>
            <p className="student-eyebrow">Fatec Tatuí</p>
            <h1>Consulta de aulas</h1>
            <p className="student-subtitle">
              Selecione o professor e a disciplina para encontrar a sala e o horário da aula.
            </p>
          </div>
          <button type="button" className="back-button" onClick={onBack}>
            ← Voltar ao painel do Diretor
          </button>
        </div>
      </header>

      <main className="student-content">
        <section className="student-card filters-card" aria-labelledby="filters-title">
          <div className="section-heading">
            <div>
              <p className="section-kicker">Filtros</p>
              <h2 id="filters-title">Encontre sua aula</h2>
            </div>
            <span className="available-filters">Professor e disciplina disponíveis</span>
          </div>

          {erroFiltros ? (
            <div className="student-message student-message--error" role="alert">
              <p>{erroFiltros}</p>
              <button type="button" onClick={() => window.location.reload()}>
                Recarregar filtros
              </button>
            </div>
          ) : (
            <div className="filters-grid">
              <label className="filter-field filter-field--pending">
                <span>Curso <em>Em breve</em></span>
                <select value={filtros.curso} disabled aria-label="Filtro de curso em breve">
                  <option>Disponível em breve</option>
                </select>
              </label>

              <label className="filter-field">
                <span>Professor</span>
                <select
                  value={filtros.professorId}
                  onChange={(event) => atualizarProfessor(event.target.value)}
                  disabled={carregandoFiltros}
                >
                  <option value="">Selecione um professor</option>
                  {professoresDisponiveis.map((docente) => (
                    <option key={docente.id} value={docente.id}>
                      {docente.nome}
                    </option>
                  ))}
                </select>
              </label>

              <label className="filter-field filter-field--pending">
                <span>Sala <em>Em breve</em></span>
                <select value={filtros.salaId} disabled aria-label="Filtro de sala em breve">
                  <option>Disponível em breve</option>
                </select>
              </label>

              <label className="filter-field">
                <span>Disciplina</span>
                <select
                  value={filtros.disciplinaId}
                  onChange={(event) => atualizarDisciplina(event.target.value)}
                  disabled={carregandoFiltros || Boolean(professorSelecionado && !professorSelecionado.disciplina_id)}
                >
                  <option value="">
                    {professorSelecionado && !professorSelecionado.disciplina_id
                      ? 'Professor sem disciplina vinculada'
                      : 'Selecione uma disciplina'}
                  </option>
                  {disciplinasDisponiveis.map((disciplina) => (
                    <option key={disciplina.id} value={disciplina.id}>
                      {disciplina.nome}
                    </option>
                  ))}
                </select>
              </label>
            </div>
          )}
        </section>

        <section className="student-card results-card" aria-live="polite" aria-labelledby="results-title">
          <div className="section-heading">
            <div>
              <p className="section-kicker">Resultado da consulta</p>
              <h2 id="results-title">Sala e horário</h2>
            </div>
            {consultaPronta && !carregandoResultados && !erroResultados && (
              <span className="result-count">
                {resultados.length} {resultados.length === 1 ? 'horário encontrado' : 'horários encontrados'}
              </span>
            )}
          </div>

          {!consultaPronta && (
            <div className="student-empty-state">
              <span className="empty-icon" aria-hidden="true">⌕</span>
              <p>Escolha um professor e uma disciplina para consultar os horários cadastrados.</p>
            </div>
          )}

          {consultaPronta && carregandoResultados && (
            <div className="student-empty-state">
              <span className="student-spinner" aria-label="Carregando horários" />
              <p>Buscando horários disponíveis...</p>
            </div>
          )}

          {consultaPronta && !carregandoResultados && erroResultados && (
            <div className="student-message student-message--error" role="alert">
              <p>{erroResultados}</p>
            </div>
          )}

          {consultaPronta && !carregandoResultados && !erroResultados && resultados.length === 0 && (
            <div className="student-empty-state">
              <span className="empty-icon" aria-hidden="true">◷</span>
              <p>
                Não há horários cadastrados para {professorSelecionado?.nome || 'este professor'} em{' '}
                {disciplinaSelecionada?.nome || 'esta disciplina'}.
              </p>
            </div>
          )}

          {consultaPronta && !carregandoResultados && !erroResultados && resultados.length > 0 && (
            <div className="results-list">
              {resultados.map((alocacao) => (
                <article key={alocacao.id} className="result-item">
                  <div className="result-date">
                    <span>Data da aula</span>
                    <strong>{formatarData(alocacao.data)}</strong>
                  </div>
                  <dl className="result-details">
                    <div>
                      <dt>Sala</dt>
                      <dd>{alocacao.sala?.nome || 'Não informada'}</dd>
                    </div>
                    <div>
                      <dt>Prédio</dt>
                      <dd>{alocacao.predio || alocacao.sala?.predio || 'Não informado'}</dd>
                    </div>
                    <div>
                      <dt>Horário</dt>
                      <dd>
                        {formatarHorario(alocacao.horario_inicio)} às {formatarHorario(alocacao.horario_fim)}
                      </dd>
                    </div>
                  </dl>
                </article>
              ))}
            </div>
          )}
        </section>
      </main>
    </div>
  );
};

export default StudentHome;
