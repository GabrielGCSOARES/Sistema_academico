import React, { useState } from 'react';
import './CursoSelecaoModal.css';

/**
 * CursoSelecaoModal - Modal para seleção de Curso, Turno e Semestre/Ano na tela do Aluno.
 * 
 * Regras:
 * - 5 Cursos com 6 Semestres:
 *   - Automação Industrial (Manhã e Noite)
 *   - Manutenção Industrial (Manhã e Noite)
 *   - Gestão da Tecnologia da Informação (Manhã e Noite)
 *   - Gestão Empresarial (Tarde e Noite)
 *   - Produção Fonográfica (Tarde)
 * - 2 Cursos Anuais com 1º e 2º Ano:
 *   - Processos Gerenciais (Tarde)
 *   - Análise e Desenvolvimento de Sistemas (Tarde)
 */
const CURSOS_CONFIG = [
  {
    id: 1,
    nome: 'Automação Industrial',
    codigo: 'AUT',
    tipoPeriodo: 'semestral',
    totalPeriodos: 6,
    turnos: ['Manhã', 'Noite'],
    descricao: 'Tecnologia em Automação Industrial',
  },
  {
    id: 2,
    nome: 'Manutenção Industrial',
    codigo: 'MAN',
    tipoPeriodo: 'semestral',
    totalPeriodos: 6,
    turnos: ['Manhã', 'Noite'],
    descricao: 'Tecnologia em Manutenção Industrial',
  },
  {
    id: 3,
    nome: 'Gestão da Tecnologia da Informação',
    codigo: 'GTI',
    tipoPeriodo: 'semestral',
    totalPeriodos: 6,
    turnos: ['Manhã', 'Noite'],
    descricao: 'Tecnologia em Gestão de TI',
  },
  {
    id: 4,
    nome: 'Gestão Empresarial',
    codigo: 'GE',
    tipoPeriodo: 'semestral',
    totalPeriodos: 6,
    turnos: ['Tarde', 'Noite'],
    descricao: 'Tecnologia em Gestão Empresarial',
  },
  {
    id: 5,
    nome: 'Produção Fonográfica',
    codigo: 'PF',
    tipoPeriodo: 'semestral',
    totalPeriodos: 6,
    turnos: ['Tarde'],
    descricao: 'Tecnologia em Produção Fonográfica',
  },
  {
    id: 6,
    nome: 'Processos Gerenciais',
    codigo: 'PG',
    tipoPeriodo: 'anual',
    totalPeriodos: 2,
    turnos: ['Tarde'],
    descricao: 'Curso Anual - 1º e 2º Ano',
  },
  {
    id: 7,
    nome: 'Análise e Desenvolvimento de Sistemas',
    codigo: 'ADS',
    tipoPeriodo: 'anual',
    totalPeriodos: 2,
    turnos: ['Tarde'],
    descricao: 'Curso Anual - 1º e 2º Ano',
  },
];

const CursoSelecaoModal = ({ cursoAtual, onClose, onConfirmar }) => {
  const [cursoSelecionado, setCursoSelecionado] = useState(() => {
    if (!cursoAtual) return CURSOS_CONFIG[0];
    return CURSOS_CONFIG.find((c) => c.nome === cursoAtual.nome) || CURSOS_CONFIG[0];
  });

  const [turnoSelecionado, setTurnoSelecionado] = useState(() => {
    if (cursoAtual?.turno && cursoSelecionado?.turnos?.includes(cursoAtual.turno)) {
      return cursoAtual.turno;
    }
    return cursoSelecionado?.turnos?.[0] || 'Tarde';
  });

  const [periodoSelecionado, setPeriodoSelecionado] = useState(() => {
    return cursoAtual?.periodoNumero || 1;
  });

  const handleSelectCurso = (curso) => {
    setCursoSelecionado(curso);
    const turnoPadrao = curso.turnos[0] || 'Tarde';
    setTurnoSelecionado(turnoPadrao);
    setPeriodoSelecionado(1);
  };

  const handleConfirmar = () => {
    if (!cursoSelecionado) return;

    const isAnual = cursoSelecionado.tipoPeriodo === 'anual';
    const periodoTipo = isAnual ? 'Ano' : 'Semestre';
    const nomeCompleto = `${cursoSelecionado.nome} - ${periodoSelecionado}º ${periodoTipo} (${turnoSelecionado})`;

    onConfirmar({
      cursoId: cursoSelecionado.id,
      nome: cursoSelecionado.nome,
      codigo: cursoSelecionado.codigo,
      turno: turnoSelecionado,
      periodoNumero: Number(periodoSelecionado),
      periodoTipo,
      nomeCompleto,
    });

    onClose();
  };

  const isAnual = cursoSelecionado.tipoPeriodo === 'anual';
  const periodoLabel = isAnual ? 'Ano' : 'Semestre';

  return (
    <div className="curso-modal-overlay" role="presentation" onClick={onClose}>
      <div
        className="curso-modal-content"
        role="dialog"
        aria-modal="true"
        onClick={(e) => e.stopPropagation()}
      >
        <header className="curso-modal-header">
          <div>
            <h2>Selecionar Curso e Grade Curricular</h2>
            <span className="curso-modal-subtitle">
              Escolha seu curso, turno e período para visualizar a grade semanal de aulas e salas
            </span>
          </div>
          <button type="button" className="curso-modal-close" onClick={onClose} aria-label="Fechar">
            &times;
          </button>
        </header>

        <div className="curso-modal-body">
          {/* Passo 1: Selecione o Curso */}
          <div className="step-section">
            <label className="step-title">
              <span className="step-number">1</span> Escolha o seu Curso:
            </label>
            <div className="cursos-cards-grid">
              {CURSOS_CONFIG.map((c) => {
                const isSelected = cursoSelecionado.id === c.id;
                return (
                  <button
                    key={c.id}
                    type="button"
                    className={`curso-card-btn ${isSelected ? 'selected' : ''}`}
                    onClick={() => handleSelectCurso(c)}
                  >
                    <div className="curso-card-header">
                      <span className="curso-code-badge">{c.codigo}</span>
                      <span className="curso-periodo-tag">
                        {c.totalPeriodos} {c.tipoPeriodo === 'anual' ? 'Anos' : 'Semestres'}
                      </span>
                    </div>
                    <strong className="curso-name">{c.nome}</strong>
                    <span className="curso-turnos-info">
                      Turno: {c.turnos.join(' e ')}
                    </span>
                  </button>
                );
              })}
            </div>
          </div>

          {/* Passo 2: Turno e Semestre/Ano */}
          <div className="step-section step-two-grid">
            {/* Seleção de Turno */}
            <div className="sub-step">
              <label className="step-title">
                <span className="step-number">2</span> Turno:
              </label>
              <div className="turnos-options-row">
                {cursoSelecionado.turnos.map((t) => {
                  const isSelected = turnoSelecionado === t;
                  return (
                    <button
                      key={t}
                      type="button"
                      className={`option-btn ${isSelected ? 'selected' : ''}`}
                      onClick={() => setTurnoSelecionado(t)}
                    >
                      {t === 'Manhã' ? '☀️ Manhã' : t === 'Tarde' ? '🌤️ Tarde' : '🌙 Noite'}
                    </button>
                  );
                })}
              </div>
            </div>

            {/* Seleção de Semestre ou Ano */}
            <div className="sub-step">
              <label className="step-title">
                <span className="step-number">3</span> {periodoLabel}:
              </label>
              <div className="periodos-options-row">
                {Array.from({ length: cursoSelecionado.totalPeriodos }, (_, i) => i + 1).map((num) => {
                  const isSelected = Number(periodoSelecionado) === num;
                  return (
                    <button
                      key={num}
                      type="button"
                      className={`option-btn option-btn-number ${isSelected ? 'selected' : ''}`}
                      onClick={() => setPeriodoSelecionado(num)}
                    >
                      {num}º {periodoLabel}
                    </button>
                  );
                })}
              </div>
            </div>
          </div>

          {/* Resumo da Escolha */}
          <div className="curso-selection-preview">
            <span className="preview-label">Grade que será consultada:</span>
            <strong className="preview-highlight">
              🎓 {cursoSelecionado.nome} — {periodoSelecionado}º {periodoLabel} ({turnoSelecionado})
            </strong>
          </div>
        </div>

        <footer className="curso-modal-footer">
          <button type="button" className="btn-cancel-curso" onClick={onClose}>
            Cancelar
          </button>
          <button type="button" className="btn-confirm-curso" onClick={handleConfirmar}>
            Confirmar e Ver Grade da Semana
          </button>
        </footer>
      </div>
    </div>
  );
};

export default CursoSelecaoModal;
