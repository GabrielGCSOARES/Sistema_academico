import React, { useMemo, useState } from 'react';
import './SalaHorarioModal.css';

const DIAS_SEMANA = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta'];
const PERIODOS = [
  { id: 'todos', label: 'Todos' },
  { id: 'matutino', label: 'Matutino' },
  { id: 'vespertino', label: 'Vespertino' },
  { id: 'noturno', label: 'Noturno' },
];

const HORARIOS_AULA = [
  { id: '07:40-08:30', inicio: '07:40', fim: '08:30', periodo: 'matutino' },
  { id: '08:30-09:20', inicio: '08:30', fim: '09:20', periodo: 'matutino' },
  { id: '09:30-10:20', inicio: '09:30', fim: '10:20', periodo: 'matutino' },
  { id: '10:20-11:10', inicio: '10:20', fim: '11:10', periodo: 'matutino' },
  { id: '11:20-12:10', inicio: '11:20', fim: '12:10', periodo: 'matutino' },
  { id: '12:10-13:00', inicio: '12:10', fim: '13:00', periodo: 'matutino' },
  { id: '14:00-14:50', inicio: '14:00', fim: '14:50', periodo: 'vespertino' },
  { id: '14:50-15:40', inicio: '14:50', fim: '15:40', periodo: 'vespertino' },
  { id: '15:50-16:40', inicio: '15:50', fim: '16:40', periodo: 'vespertino' },
  { id: '16:40-17:30', inicio: '16:40', fim: '17:30', periodo: 'vespertino' },
  { id: '17:30-18:20', inicio: '17:30', fim: '18:20', periodo: 'vespertino' },
  { id: '19:00-19:50', inicio: '19:00', fim: '19:50', periodo: 'noturno' },
  { id: '19:50-20:40', inicio: '19:50', fim: '20:40', periodo: 'noturno' },
  { id: '20:50-21:40', inicio: '20:50', fim: '21:40', periodo: 'noturno' },
  { id: '21:40-22:30', inicio: '21:40', fim: '22:30', periodo: 'noturno' },
];

const toMinutes = (time) => {
  const [hours, minutes] = time.split(':').map(Number);
  return hours * 60 + minutes;
};

const getDataSemana = (indiceDia) => {
  const hoje = new Date();
  const diaSemana = hoje.getDay();
  const distanciaSegunda = diaSemana === 0 ? 1 : 1 - diaSemana;
  const data = new Date(hoje);
  data.setDate(hoje.getDate() + distanciaSegunda + indiceDia);

  const ano = data.getFullYear();
  const mes = String(data.getMonth() + 1).padStart(2, '0');
  const dia = String(data.getDate()).padStart(2, '0');

  return `${ano}-${mes}-${dia}`;
};

const normalizeDate = (date) => {
  if (!date) return '';
  const str = String(date);
  if (str.includes('T')) {
    return str.split('T')[0];
  }
  return str.slice(0, 10);
};

const normalizeTime = (time) => String(time || '').slice(0, 5);
const professorTemDisciplina = (professor) => Boolean(
  professor?.disciplina
  && professor.disciplina !== 'Sem disciplina'
  && professor.disciplina !== 'Disciplina não informada'
);
const formatDateBR = (date) => {
  const [year, month, day] = normalizeDate(date).split('-');
  return `${day}/${month}/${year}`;
};

const SalaHorarioModal = ({ sala, predio, professores, alocacoes = [], onClose, onHorarioSelecionado, onDesalocar, onOpenVinculoModal }) => {
  const [selectedProfessorId, setSelectedProfessorId] = useState(null);
  const [periodo, setPeriodo] = useState('todos');

  const horariosAula = useMemo(
    () => HORARIOS_AULA.filter((horario) => periodo === 'todos' || horario.periodo === periodo),
    [periodo]
  );
  const alocacoesDaSala = useMemo(
    () => alocacoes
      .filter((alocacao) => (
        Number(alocacao.sala_id) === Number(sala?.id)
        && (!alocacao.predio || !predio || alocacao.predio === predio)
      ))
      .sort((a, b) => {
        const dataA = `${normalizeDate(a.data)} ${normalizeTime(a.horario_inicio)}`;
        const dataB = `${normalizeDate(b.data)} ${normalizeTime(b.horario_inicio)}`;
        return dataA.localeCompare(dataB);
      }),
    [alocacoes, predio, sala?.id]
  );

  if (!sala) {
    return null;
  }

  const professorSelecionado = professores.find(
    (professor) => String(professor.id) === String(selectedProfessorId)
  );
  const podeAlocar = professorTemDisciplina(professorSelecionado);

  const getAlocacoesHorario = (data, horario) => {
    const inicioSlot = toMinutes(horario.inicio);
    const fimSlot = toMinutes(horario.fim);

    return alocacoes.filter((alocacao) => {
      const alocacaoInicio = toMinutes(normalizeTime(alocacao.horario_inicio));
      const alocacaoFim = alocacao.horario_fim
        ? toMinutes(normalizeTime(alocacao.horario_fim))
        : alocacaoInicio + 50;

      return Number(alocacao.sala_id) === Number(sala.id)
        && normalizeDate(alocacao.data) === data
        && (!alocacao.predio || alocacao.predio === predio)
        && alocacaoInicio < fimSlot
        && alocacaoFim > inicioSlot;
    });
  };

  const handleHorarioClick = (data, horario) => {
    if (!selectedProfessorId) {
      return;
    }

    if (onHorarioSelecionado) {
      onHorarioSelecionado({
        professorId: selectedProfessorId,
        salaId: sala.id,
        predio,
        data,
        horarioInicio: horario.inicio,
        horarioFim: horario.fim,
      });
    }
  };

  const handleDesalocarClick = (alocacaoId) => {
    onDesalocar(alocacaoId);
  };

  return (
    <div className="sala-modal-overlay" role="presentation" onMouseDown={onClose}>
      <section
        className="sala-modal-content"
        role="dialog"
        aria-modal="true"
        aria-labelledby="sala-modal-title"
        onMouseDown={(event) => event.stopPropagation()}
      >
        <header className="sala-modal-header">
          <div>
            <span className="sala-modal-kicker">{predio}</span>
            <h2 id="sala-modal-title">{sala.nome}</h2>
          </div>
          <button className="sala-modal-close" type="button" onClick={onClose} aria-label="Fechar modal">
            &times;
          </button>
        </header>

        <div className="sala-modal-grid">
          <aside className="sala-modal-panel sala-modal-docentes">
            <div className="sala-modal-panel-header">
              <h3>Selecione o Professor</h3>
              <span>{professores.length}</span>
            </div>

            <div className="docentes-api-slot" data-api-slot="docentes">
              {professores.map((professor) => {
                const temDisciplina = professorTemDisciplina(professor);

                return (
                  <div  
                    key={professor.id}
                    className={`docente-row ${selectedProfessorId === professor.id ? 'selected' : ''}`}
                    onClick={() => setSelectedProfessorId(professor.id)}
                    role="button"
                    tabIndex={0}
                  >
                    <div className="docente-row-main">
                      <strong>{professor.nome}</strong>
                      <span className={`disciplina-subtext ${temDisciplina ? 'tem-disc' : 'sem-disc'}`}>
                        {temDisciplina ? `📚 ${professor.disciplina}` : '⚠️ Sem disciplina vinculada'}
                      </span>
                    </div>

                    {onOpenVinculoModal && (
                      <button
                        type="button"
                        className="btn-vincular-mini"
                        onClick={(e) => {
                          e.stopPropagation();
                          const docId = String(professor.id).startsWith('docente-')
                            ? Number(String(professor.id).replace('docente-', ''))
                            : Number(professor.id);
                          onOpenVinculoModal(docId);
                        }}
                        title="Atribuir ou cadastrar disciplina"
                      >
                        {temDisciplina ? '✏️' : '➕'}
                      </button>
                    )}
                  </div>
                );
              })}
            </div>

            <div className="alocacoes-sala">
              <div className="alocacoes-sala-header">
                <h3>Alocações desta sala</h3>
                <span>{alocacoesDaSala.length}</span>
              </div>

              <div className="alocacoes-sala-list">
                {alocacoesDaSala.length === 0 ? (
                  <p className="alocacoes-vazias">Nenhum professor alocado nesta sala.</p>
                ) : (
                  alocacoesDaSala.map((alocacao) => (
                    <article className="alocacao-row" key={alocacao.id}>
                      <div>
                        <strong>{alocacao.professor?.nome || 'Professor'}</strong>
                        <span className="aloc-disc-tag">{alocacao.professor?.disciplina || ''}</span>
                        <span>
                          {formatDateBR(alocacao.data)} · {normalizeTime(alocacao.horario_inicio)}
                          {alocacao.horario_fim ? ` às ${normalizeTime(alocacao.horario_fim)}` : ''}
                        </span>
                      </div>
                      <button type="button" onClick={() => handleDesalocarClick(alocacao.id)}>
                        Retirar
                      </button>
                    </article>
                  ))
                )}
              </div>
            </div>
          </aside>

          <section className="sala-modal-panel sala-modal-horarios">
            <div className="sala-modal-panel-header">
              <div>
                <h3>Horários das Aulas</h3>
                <span>
                  {selectedProfessorId && !podeAlocar
                    ? 'Vincule uma disciplina ao professor selecionado para liberar os horários'
                    : 'Selecione um professor com disciplina e clique no horário para inserir'}
                </span>
              </div>
              <div className="periodo-filter" aria-label="Filtro de período">
                {PERIODOS.map((item) => (
                  <button
                    className={periodo === item.id ? 'active' : ''}
                    type="button"
                    key={item.id}
                    onClick={() => setPeriodo(item.id)}
                  >
                    {item.label}
                  </button>
                ))}
              </div>
            </div>

            <div
              className={`horarios-api-slot ${!podeAlocar ? 'horarios-bloqueados' : ''}`}
              data-api-slot="horarios-aulas"
              aria-disabled={!podeAlocar}
            >
              {!podeAlocar && (
                <p className="horarios-bloqueados-aviso">
                  {selectedProfessorId
                    ? 'Este professor ainda não possui disciplina vinculada.'
                    : 'Selecione um professor com disciplina para liberar a grade.'}
                </p>
              )}
              <div className="dias-semana-grid">
                {DIAS_SEMANA.map((dia, indiceDia) => {
                  const data = getDataSemana(indiceDia);

                  return (
                    <div className="dia-coluna" key={dia}>
                      <h4>{dia}</h4>
                      <div className="horarios-lista">
                        {horariosAula.map((horario) => {
                          const alocacoesSlot = getAlocacoesHorario(data, horario);

                          if (alocacoesSlot.length > 0) {
                            return (
                              <article className="horario-bloco ocupado" key={`${dia}-${horario.id}`}>
                                {alocacoesSlot.map((aloc) => (
                                  <div key={aloc.id} className="alocacao-item-sub">
                                    <strong>{aloc.professor?.nome || 'Professor'}</strong>
                                    {aloc.professor?.disciplina && (
                                      <span className="bloco-disciplina">{aloc.professor.disciplina}</span>
                                    )}
                                    <button 
                                      type="button" 
                                      className="btn-retirar-micro"
                                      onClick={() => handleDesalocarClick(aloc.id)}
                                    >
                                      Retirar
                                    </button>
                                  </div>
                                ))}
                                <button
                                  type="button"
                                  className="btn-inserir-novamente"
                                  onClick={() => handleHorarioClick(data, horario)}
                                  disabled={!podeAlocar}
                                  title={podeAlocar ? 'Clique para inserir o professor neste horário' : 'Selecione um professor com disciplina'}
                                >
                                  ➕ Inserir neste horário
                                </button>
                              </article>
                            );
                          }

                          return (
                            <button
                              className="horario-bloco"
                              type="button"
                              key={`${dia}-${horario.id}`}
                              onClick={() => handleHorarioClick(data, horario)}
                              disabled={!podeAlocar}
                              title={podeAlocar ? 'Clique para alocar neste horário' : 'Selecione um professor com disciplina'}
                            >
                              <span>{horario.inicio}</span>
                              <span>{horario.fim}</span>
                            </button>
                          );
                        })}
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>
          </section>
        </div>
      </section>
    </div>
  );
};

export default SalaHorarioModal;
