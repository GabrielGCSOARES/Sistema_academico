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
  const distanciaSegunda = diaSemana === 0 ? -6 : 1 - diaSemana;
  const data = new Date(hoje);
  data.setDate(hoje.getDate() + distanciaSegunda + indiceDia);

  const ano = data.getFullYear();
  const mes = String(data.getMonth() + 1).padStart(2, '0');
  const dia = String(data.getDate()).padStart(2, '0');

  return `${ano}-${mes}-${dia}`;
};

const normalizeDate = (date) => String(date || '').slice(0, 10);
const normalizeTime = (time) => String(time || '').slice(0, 5);
const formatDateBR = (date) => {
  const [year, month, day] = normalizeDate(date).split('-');
  return `${day}/${month}/${year}`;
};

const SalaHorarioModal = ({ sala, predio, professores, alocacoes = [], onClose, onAlocar, onDesalocar }) => {
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
        && (!alocacao.predio || alocacao.predio === predio)
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

  const getAlocacaoHorario = (data, horario) => {
    const inicioSlot = toMinutes(horario.inicio);
    const fimSlot = toMinutes(horario.fim);

    return alocacoes.find((alocacao) => {
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

  const handleHorarioClick = (data, horario, alocacao) => {
    if (!selectedProfessorId || alocacao) {
      return;
    }

    onAlocar({
      professorId: selectedProfessorId,
      salaId: sala.id,
      predio,
      data,
      horarioInicio: horario.inicio,
      horarioFim: horario.fim,
    });
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
              <h3>Docentes</h3>
              <span>{professores.length}</span>
            </div>

            <div className="docentes-api-slot" data-api-slot="docentes">
              {professores.map((professor) => (
                <button
                  className={`docente-row ${selectedProfessorId === professor.id ? 'selected' : ''}`}
                  type="button"
                  key={professor.id}
                  onClick={() => setSelectedProfessorId(professor.id)}
                >
                  <strong>{professor.nome}</strong>
                  <span>{professor.disciplina || 'Disciplina não informada'}</span>
                </button>
              ))}
            </div>

            <div className="alocacoes-sala">
              <div className="alocacoes-sala-header">
                <h3>Alocações da sala</h3>
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
                <h3>Horários das aulas</h3>
                <span>Seg. a Sex.</span>
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

            <div className="horarios-api-slot" data-api-slot="horarios-aulas">
              <div className="dias-semana-grid">
                {DIAS_SEMANA.map((dia, indiceDia) => {
                  const data = getDataSemana(indiceDia);

                  return (
                  <div className="dia-coluna" key={dia}>
                    <h4>{dia}</h4>
                    <div className="horarios-lista">
                      {horariosAula.map((horario) => {
                        const alocacao = getAlocacaoHorario(data, horario);
                        const professorNome = alocacao?.professor?.nome || 'Professor';

                        if (alocacao) {
                          return (
                            <article className="horario-bloco ocupado" key={`${dia}-${horario.id}`}>
                              <strong>{professorNome}</strong>
                              <span>Ocupado</span>
                              <button type="button" onClick={() => handleDesalocarClick(alocacao.id)}>
                                Retirar
                              </button>
                            </article>
                          );
                        }

                        return (
                          <button
                            className="horario-bloco"
                            type="button"
                            key={`${dia}-${horario.id}`}
                            onClick={() => handleHorarioClick(data, horario, alocacao)}
                            disabled={!selectedProfessorId}
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
