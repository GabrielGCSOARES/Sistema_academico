import React, { useState, useEffect, useMemo } from 'react';
import './AlocacaoModal.css';
import { directorService } from '../../services/api';

/**
 * AlocacaoModal - Modal responsável pela atribuição/alocação de disciplina a um professor.
 * 
 * Regra de Negócio:
 * - Cada disciplina só pode ser lecionada por 1 professor.
 * - Disciplinas já atribuídas a outros professores ficam bloqueadas (🔒).
 * - A alocação de professor em sala ocorre posteriormente no SalaHorarioModal.
 */
const AlocacaoModal = ({ professor, selectedDocenteId, onClose, onSaved }) => {
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  const [docentes, setDocentes] = useState([]);
  const [disciplinas, setDisciplinas] = useState([]);

  // Determinar o ID do docente ativo (pode vir via prop `professor` ou `selectedDocenteId`)
  const initialDocId = useMemo(() => {
    const rawId = selectedDocenteId || professor?.id;
    if (!rawId) return '';
    return String(rawId).startsWith('docente-')
      ? String(rawId).replace('docente-', '')
      : String(rawId);
  }, [selectedDocenteId, professor]);

  const [docenteId, setDocenteId] = useState(initialDocId);
  const [disciplinaId, setDisciplinaId] = useState('');
  const [novaDisciplinaNome, setNovaDisciplinaNome] = useState('');
  const [criarNova, setCriarNova] = useState(false);

  // Carregar dados de docentes e disciplinas
  useEffect(() => {
    let isMounted = true;
    const fetchData = async () => {
      try {
        setLoading(true);
        setError(null);

        const [docRes, discRes] = await Promise.all([
          directorService.getDocentes(),
          directorService.getDisciplinas(),
        ]);

        if (!isMounted) return;

        const listDocentes = docRes.data || [];
        const listDisciplinas = discRes.data || [];

        setDocentes(listDocentes);
        setDisciplinas(listDisciplinas);

        let activeDocId = initialDocId;
        if (!activeDocId && listDocentes.length > 0) {
          activeDocId = String(listDocentes[0].id);
        }
        setDocenteId(activeDocId);

        // Pré-selecionar disciplina do docente se já possuir
        const activeDocente = listDocentes.find(d => Number(d.id) === Number(activeDocId));
        if (activeDocente && activeDocente.disciplina_id) {
          setDisciplinaId(String(activeDocente.disciplina_id));
        } else if (listDisciplinas.length > 0) {
          setDisciplinaId(String(listDisciplinas[0].id));
        }
      } catch (err) {
        if (isMounted) {
          setError('Erro ao carregar lista de professores e disciplinas.');
          console.error(err);
        }
      } finally {
        if (isMounted) setLoading(false);
      }
    };

    fetchData();
    return () => { isMounted = false; };
  }, [initialDocId]);

  // Mapeamento de disciplinas ocupadas por OUTROS docentes
  const disciplinasOcupadas = useMemo(() => {
    const mapa = {};
    docentes.forEach((doc) => {
      if (doc.disciplina_id && Number(doc.id) !== Number(docenteId)) {
        mapa[doc.disciplina_id] = doc.nome;
      }
    });
    return mapa;
  }, [docentes, docenteId]);

  const docenteSelecionado = useMemo(() => {
    return docentes.find(d => Number(d.id) === Number(docenteId)) || professor;
  }, [docentes, docenteId, professor]);

  const disciplinaSelecionada = useMemo(() => {
    return disciplinas.find(d => Number(d.id) === Number(disciplinaId));
  }, [disciplinas, disciplinaId]);

  const nomeDisciplinaPreview = criarNova
    ? (novaDisciplinaNome.trim() || 'Nova Disciplina')
    : (disciplinaSelecionada?.nome || 'Selecione uma disciplina');

  const handleDocenteChange = (newDocId) => {
    setDocenteId(newDocId);
    const doc = docentes.find(d => Number(d.id) === Number(newDocId));
    if (doc && doc.disciplina_id) {
      setDisciplinaId(String(doc.disciplina_id));
    }
  };

  const handleConfirmar = async () => {
    if (!docenteId) {
      alert('Por favor, selecione um professor.');
      return;
    }

    if (criarNova && !novaDisciplinaNome.trim()) {
      alert('Por favor, digite o nome da nova disciplina.');
      return;
    }

    if (!criarNova && !disciplinaId) {
      alert('Por favor, selecione uma disciplina.');
      return;
    }

    setSaving(true);
    try {
      const payload = {
        docente_id: Number(docenteId),
      };

      if (criarNova) {
        payload.disciplina_nome = novaDisciplinaNome.trim();
      } else {
        payload.disciplina_id = Number(disciplinaId);
      }

      const response = await directorService.vincularDocenteDisciplina(payload);

      const discNome = response?.data?.disciplina?.nome ||
        payload.disciplina_nome ||
        disciplinaSelecionada?.nome ||
        'Disciplina';

      if (onSaved) {
        await onSaved({
          docente_id: Number(docenteId),
          disciplina_id: response?.data?.disciplina?.id || Number(disciplinaId),
          disciplina_nome: discNome,
        });
      }

      onClose();
    } catch (err) {
      const msg = err?.response?.data?.message || 'Erro ao atribuir disciplina ao professor.';
      alert(msg);
      console.error('Erro na alocação de disciplina:', err);
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="alocacao-modal-overlay" role="presentation" onClick={onClose}>
      <div 
        className="alocacao-modal-content" 
        role="dialog" 
        aria-modal="true" 
        onClick={(e) => e.stopPropagation()}
      >
        {/* Header */}
        <div className="alocacao-modal-header">
          <div className="header-title-box">
            <h2>Alocar Professor a uma Disciplina</h2>
            <span className="header-subtitle">Vincule a matéria que este docente irá lecionar</span>
          </div>
          <button type="button" className="alocacao-modal-close" onClick={onClose} aria-label="Fechar">
            &times;
          </button>
        </div>

        {/* Body */}
        <div className="alocacao-modal-body">
          {loading && (
            <div className="modal-state-box loading">
              <div className="spinner"></div>
              <p>Carregando dados de professores e disciplinas...</p>
            </div>
          )}

          {error && (
            <div className="modal-state-box error">
              <p>{error}</p>
            </div>
          )}

          {!loading && !error && (
            <>
              {/* Card de seleção de professor */}
              <div className="form-group">
                <label className="form-label">Professor / Docente:</label>
                <select
                  className="form-select"
                  value={docenteId}
                  onChange={(e) => handleDocenteChange(e.target.value)}
                  disabled={!!initialDocId}
                >
                  {docentes.map((d) => (
                    <option key={d.id} value={d.id}>
                      {d.nome}
                    </option>
                  ))}
                </select>
              </div>

              {/* Preview de Vínculo */}
              <div className="vinculo-preview-card">
                <div className="preview-item">
                  <span className="preview-label">Professor:</span>
                  <span className="preview-value professor-name">
                    {docenteSelecionado?.nome || 'Professor'}
                  </span>
                </div>
                <div className="preview-arrow">➔</div>
                <div className="preview-item">
                  <span className="preview-label">Disciplina:</span>
                  <span className="preview-value disciplina-badge">
                    {nomeDisciplinaPreview}
                  </span>
                </div>
              </div>

              {/* Seleção de Disciplina */}
              <div className="form-group">
                <div className="form-label-row">
                  <label className="form-label">
                    {criarNova ? 'Nome da Nova Disciplina:' : 'Selecione a Disciplina:'}
                  </label>
                  <button
                    type="button"
                    className="toggle-mode-button"
                    onClick={() => setCriarNova(!criarNova)}
                  >
                    {criarNova ? 'Selecionar Existente' : 'Cadastrar Nova Disciplina'}
                  </button>
                </div>

                {criarNova ? (
                  <input
                    type="text"
                    className="form-input"
                    placeholder="Ex: Inteligência Artificial II, Robótica..."
                    value={novaDisciplinaNome}
                    onChange={(e) => setNovaDisciplinaNome(e.target.value)}
                    autoFocus
                  />
                ) : (
                  <select
                    className="form-select"
                    value={disciplinaId}
                    onChange={(e) => setDisciplinaId(e.target.value)}
                  >
                    {disciplinas.map((disc) => {
                      const ocupadaPor = disciplinasOcupadas[disc.id];
                      return (
                        <option key={disc.id} value={disc.id} disabled={!!ocupadaPor}>
                          {disc.nome}{ocupadaPor ? ` (Já lecionada por ${ocupadaPor})` : ''}
                        </option>
                      );
                    })}
                  </select>
                )}
                
                <p className="field-help-text">
                  Disciplinas lecionadas por outros professores ficam bloqueadas para garantir exclusividade.
                </p>
              </div>
            </>
          )}
        </div>

        {/* Footer */}
        <div className="alocacao-modal-footer">
          <button type="button" className="btn-modal-cancel" onClick={onClose}>
            Cancelar
          </button>
          <button
            type="button"
            className="btn-modal-confirm"
            onClick={handleConfirmar}
            disabled={!docenteId || (criarNova ? !novaDisciplinaNome.trim() : !disciplinaId) || saving}
          >
            {saving ? 'Alocando...' : 'Confirmar Alocação por Disciplina'}
          </button>
        </div>
      </div>
    </div>
  );
};

export default AlocacaoModal;
