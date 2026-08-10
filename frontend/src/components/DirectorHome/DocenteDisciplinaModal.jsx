import React, { useEffect, useState, useMemo } from 'react';
import './DocenteDisciplinaModal.css';
import { directorService } from '../../services/api';

const DocenteDisciplinaModal = ({ onClose, onSaved, selectedDocenteId = null }) => {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const [docentes, setDocentes] = useState([]);
  const [disciplinas, setDisciplinas] = useState([]);

  const [docenteId, setDocenteId] = useState(selectedDocenteId ? String(selectedDocenteId) : '');
  const [disciplinaId, setDisciplinaId] = useState('');
  const [novaDisciplinaNome, setNovaDisciplinaNome] = useState('');
  const [criarNova, setCriarNova] = useState(false);
  const [saving, setSaving] = useState(false);

  const docenteSelecionado = docentes.find(
    d => Number(d.id) === Number(docenteId)
  );

  const disciplinaSelecionada = disciplinas.find(
    d => Number(d.id) === Number(disciplinaId)
  );

  // Mapa de disciplinas já atribuídas a outros docentes
  const disciplinasOcupadas = useMemo(() => {
    const mapa = {};
    docentes.forEach((doc) => {
      if (doc.disciplina_id && Number(doc.id) !== Number(docenteId)) {
        mapa[doc.disciplina_id] = doc.nome;
      }
    });
    return mapa;
  }, [docentes, docenteId]);

  const nomeDisciplinaExibicao = criarNova
    ? (novaDisciplinaNome.trim() || 'Nova Disciplina')
    : (disciplinaSelecionada?.nome || 'Selecione a disciplina');

  useEffect(() => {
    const load = async () => {
      try {
        setLoading(true);
        setError(null);

        const [docRes, discRes] = await Promise.all([
          directorService.getDocentes(),
          directorService.getDisciplinas(),
        ]);

        const listDocentes = docRes.data || [];
        const listDisciplinas = discRes.data || [];

        setDocentes(listDocentes);
        setDisciplinas(listDisciplinas);

        let activeDocId = selectedDocenteId ? String(selectedDocenteId) : '';
        if (!activeDocId && listDocentes.length > 0) {
          activeDocId = String(listDocentes[0].id);
        }
        setDocenteId(activeDocId);

        const activeDocente = listDocentes.find(d => Number(d.id) === Number(activeDocId));
        if (activeDocente && activeDocente.disciplina_id) {
          setDisciplinaId(String(activeDocente.disciplina_id));
        } else if (listDisciplinas.length > 0) {
          setDisciplinaId(String(listDisciplinas[0].id));
        }
      } catch (e) {
        setError('Erro ao carregar docentes/disciplinas.');
        console.error(e);
      } finally {
        setLoading(false);
      }
    };

    load();
  }, [selectedDocenteId]);

  const handleDocenteChange = (newDocId) => {
    setDocenteId(newDocId);
    const doc = docentes.find(d => Number(d.id) === Number(newDocId));
    if (doc && doc.disciplina_id) {
      setDisciplinaId(String(doc.disciplina_id));
    }
  };

  const handleSubmit = async () => {
    if (!docenteId) {
      alert("Selecione um docente.");
      return;
    }

    if (criarNova && !novaDisciplinaNome.trim()) {
      alert("Digite o nome da nova disciplina.");
      return;
    }

    if (!criarNova && !disciplinaId) {
      alert("Selecione uma disciplina da lista.");
      return;
    }

    try {
      setSaving(true);

      const payload = {
        docente_id: Number(docenteId),
      };

      if (criarNova) {
        payload.disciplina_nome = novaDisciplinaNome.trim();
      } else {
        payload.disciplina_id = Number(disciplinaId);
      }

      const response = await directorService.vincularDocenteDisciplina(payload);

      const discSalvaNome = response?.data?.disciplina?.nome ||
        payload.disciplina_nome ||
        disciplinaSelecionada?.nome ||
        'Nova Disciplina';

      if (onSaved) {
        await onSaved({
          docente_id: Number(docenteId),
          disciplina_id: response?.data?.disciplina?.id || Number(disciplinaId),
          disciplina_nome: discSalvaNome,
        });
      }

      alert(`Disciplina "${discSalvaNome}" atribuída e salva no professor com sucesso!`);
      onClose();
    } catch (err) {
      console.error(err);
      alert(
        err?.response?.data?.message ||
        "Não foi possível vincular a disciplina ao professor."
      );
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="modal-overlay" role="presentation" onClick={onClose}>
      <div 
        className="modal-content docente-disciplina-modal" 
        role="dialog" 
        aria-modal="true"
        onClick={(e) => e.stopPropagation()}
      >
        <div className="modal-header">
          <h2>Alocar Professor a uma Disciplina</h2>
          <button className="close-button" onClick={onClose} aria-label="Fechar">
            &times;
          </button>
        </div>

        <div className="modal-body">
          {loading && <p className="loading-text">Carregando professores e disciplinas...</p>}
          {error && <p className="error-text">{error}</p>}

          {!loading && !error && (
            <>
              <div className="field">
                <label>Professor / Docente</label>
                <select
                  value={docenteId}
                  onChange={(e) => handleDocenteChange(e.target.value)}
                  disabled={!!selectedDocenteId}
                >
                  {docentes.map((d) => (
                    <option key={d.id} value={d.id}>
                      {d.nome}
                    </option>
                  ))}
                </select>
              </div>

              <div className="preview-vinculo">
                <span className="docente-preview-nome">
                  {docenteSelecionado?.nome || "Selecione o docente"}
                </span>
                <span className="arrow">➔</span>
                <span className="badge-disciplina">
                  {nomeDisciplinaExibicao}
                </span>
              </div>

              <div className="field">
                <div className="label-with-toggle">
                  <label>{criarNova ? "Digite a Nova Disciplina" : "Selecione a Disciplina"}</label>
                  <button 
                    type="button" 
                    className="toggle-criacao-btn" 
                    onClick={() => setCriarNova(!criarNova)}
                  >
                    {criarNova ? "Selecionar Existente" : "Digitar Nova Disciplina"}
                  </button>
                </div>

                {criarNova ? (
                  <input
                    type="text"
                    className="input-nova-disciplina"
                    placeholder="Ex: Inteligência Artificial II, Robótica Avançada..."
                    value={novaDisciplinaNome}
                    onChange={(e) => setNovaDisciplinaNome(e.target.value)}
                    autoFocus
                  />
                ) : (
                  <select value={disciplinaId} onChange={(e) => setDisciplinaId(e.target.value)}>
                    {disciplinas.map((disc) => {
                      const ocupadaPor = disciplinasOcupadas[disc.id];
                      return (
                        <option key={disc.id} value={disc.id} disabled={!!ocupadaPor}>
                          {disc.nome}{ocupadaPor ? ` (${ocupadaPor})` : ''}
                        </option>
                      );
                    })}
                  </select>
                )}
              </div>
            </>
          )}
        </div>

        <div className="modal-footer">
          <button className="cancel-button" onClick={onClose}>
            Cancelar
          </button>
          <button 
            className="confirm-button" 
            onClick={handleSubmit} 
            disabled={!docenteId || (criarNova ? !novaDisciplinaNome.trim() : !disciplinaId) || saving}
          >
            {saving ? "Salvando..." : "Confirmar Alocação por Disciplina"}
          </button>
        </div>
      </div>
    </div>
  );
};

export default DocenteDisciplinaModal;
