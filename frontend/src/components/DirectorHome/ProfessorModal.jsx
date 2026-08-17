import React, { useState, useEffect, useMemo } from 'react';
import './ProfessorModal.css';
import { directorService } from '../../services/api';

/**
 * ProfessorModal - Modal para Adicionar ou Editar Professor.
 * Permite cadastrar novo docente com disciplina opcional ou renomear professor existente.
 */
const ProfessorModal = ({ mode = 'create', professor = null, onClose, onSaved }) => {
  const [nome, setNome] = useState(professor ? professor.nome : '');
  const [disciplinas, setDisciplinas] = useState([]);
  const [docentes, setDocentes] = useState([]);
  const [disciplinaId, setDisciplinaId] = useState('');
  const [criarNova, setCriarNova] = useState(false);
  const [novaDisciplinaNome, setNovaDisciplinaNome] = useState('');
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (mode === 'create') {
      let isMounted = true;
      const load = async () => {
        try {
          setLoading(true);
          const [discRes, docRes] = await Promise.all([
            directorService.getDisciplinas(),
            directorService.getDocentes(),
          ]);
          if (isMounted) {
            setDisciplinas(discRes.data || []);
            setDocentes(docRes.data || []);
          }
        } catch (err) {
          console.error('Erro ao carregar dados para o modal de professor:', err);
        } finally {
          if (isMounted) setLoading(false);
        }
      };
      load();
      return () => { isMounted = false; };
    }
  }, [mode]);

  const disciplinasOcupadas = useMemo(() => {
    const mapa = {};
    docentes.forEach((doc) => {
      if (doc.disciplina_id) {
        mapa[doc.disciplina_id] = doc.nome;
      }
    });
    return mapa;
  }, [docentes]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    const nomeLimpo = nome.trim();
    if (!nomeLimpo) {
      alert('Por favor, informe o nome do professor.');
      return;
    }

    setSaving(true);
    setError(null);

    try {
      if (mode === 'create') {
        const payload = { nome: nomeLimpo };
        if (criarNova && novaDisciplinaNome.trim()) {
          payload.disciplina_nome = novaDisciplinaNome.trim();
        } else if (!criarNova && disciplinaId) {
          payload.disciplina_id = Number(disciplinaId);
        }

        const response = await directorService.criarProfessor(payload);
        const novoProfessor = response?.data?.professor;
        if (onSaved) {
          await onSaved(novoProfessor || { nome: nomeLimpo });
        }
      } else {
        // Edit mode
        const profId = String(professor.id).startsWith('docente-')
          ? Number(String(professor.id).replace('docente-', ''))
          : Number(professor.id);

        const response = await directorService.editarProfessor(profId, { nome: nomeLimpo });
        const profAtualizado = response?.data?.professor;
        if (onSaved) {
          await onSaved(profAtualizado || { ...professor, nome: nomeLimpo });
        }
      }
      onClose();
    } catch (err) {
      const msg = err?.response?.data?.message || 'Erro ao salvar professor. Tente novamente.';
      setError(msg);
      console.error('Erro ao salvar professor:', err);
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="professor-modal-overlay" role="presentation" onClick={onClose}>
      <div
        className="professor-modal-content"
        role="dialog"
        aria-modal="true"
        onClick={(e) => e.stopPropagation()}
      >
        <header className="professor-modal-header">
          <div className="professor-header-title">
            <h2>{mode === 'create' ? 'Adicionar Novo Professor' : 'Editar Professor'}</h2>
            <span className="professor-header-subtitle">
              {mode === 'create'
                ? 'Cadastre um novo docente no sistema em tempo real'
                : `Alterando dados de: ${professor?.nome || ''}`}
            </span>
          </div>
          <button type="button" className="professor-modal-close" onClick={onClose} aria-label="Fechar">
            &times;
          </button>
        </header>

        <form onSubmit={handleSubmit} className="professor-modal-body">
          {error && (
            <div className="professor-modal-error">
              <span>⚠️ {error}</span>
            </div>
          )}

          <div className="form-group">
            <label htmlFor="input-prof-nome">
              Nome do Professor <span className="required">*</span>
            </label>
            <input
              id="input-prof-nome"
              type="text"
              className="form-control"
              placeholder="Ex: Prof. Dr. Carlos Silva"
              value={nome}
              onChange={(e) => setNome(e.target.value)}
              disabled={saving}
              autoFocus
              required
            />
          </div>

          {mode === 'create' && (
            <div className="form-group">
              <label htmlFor="select-prof-disc">Disciplina (Opcional)</label>
              
              {!criarNova ? (
                <>
                  <select
                    id="select-prof-disc"
                    className="form-control"
                    value={disciplinaId}
                    onChange={(e) => setDisciplinaId(e.target.value)}
                    disabled={loading || saving}
                  >
                    <option value="">Nenhuma disciplina vinculada</option>
                    {disciplinas.map((disc) => {
                      const ocupadoPor = disciplinasOcupadas[disc.id];
                      return (
                        <option
                          key={disc.id}
                          value={disc.id}
                          disabled={Boolean(ocupadoPor)}
                        >
                          {disc.nome} {ocupadoPor ? `(🔒 Já com: ${ocupadoPor})` : ''}
                        </option>
                      );
                    })}
                  </select>

                  <button
                    type="button"
                    className="btn-link-criar"
                    onClick={() => {
                      setCriarNova(true);
                      setDisciplinaId('');
                    }}
                  >
                    ➕ Cadastrar nova matéria...
                  </button>
                </>
              ) : (
                <div className="nova-disciplina-box">
                  <input
                    type="text"
                    className="form-control"
                    placeholder="Digite o nome da nova matéria"
                    value={novaDisciplinaNome}
                    onChange={(e) => setNovaDisciplinaNome(e.target.value)}
                    disabled={saving}
                  />
                  <button
                    type="button"
                    className="btn-link-voltar"
                    onClick={() => {
                      setCriarNova(false);
                      setNovaDisciplinaNome('');
                    }}
                  >
                    ← Selecionar matéria existente
                  </button>
                </div>
              )}
            </div>
          )}

          <footer className="professor-modal-footer">
            <button
              type="button"
              className="btn-modal-cancel"
              onClick={onClose}
              disabled={saving}
            >
              Cancelar
            </button>
            <button
              type="submit"
              className="btn-modal-save"
              disabled={saving || !nome.trim()}
            >
              {saving ? 'Salvando...' : mode === 'create' ? 'Cadastrar Professor' : 'Salvar Alterações'}
            </button>
          </footer>
        </form>
      </div>
    </div>
  );
};

export default ProfessorModal;
