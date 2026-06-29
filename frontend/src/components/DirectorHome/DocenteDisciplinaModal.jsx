import React, { useEffect, useState } from 'react';
import './DocenteDisciplinaModal.css';
import { directorService } from '../../services/api';

const DocenteDisciplinaModal = ({ onClose, onSaved, selectedDocenteId = null }) => {
const [loading, setLoading] = useState(true);
const [error, setError] = useState(null);

const [docentes, setDocentes] = useState([]);
const [disciplinas, setDisciplinas] = useState([]);

const [docenteId, setDocenteId] = useState(selectedDocenteId ? String(selectedDocenteId) : '');
const [disciplinaId, setDisciplinaId] = useState('');
const [saving, setSaving] = useState(false);

const docenteSelecionado = docentes.find(
    d => Number(d.id) === Number(docenteId)
);

const disciplinaSelecionada = disciplinas.find(
    d => Number(d.id) === Number(disciplinaId)
);
  useEffect(() => { // eslint-disable-line react-hooks/exhaustive-deps
    const load = async () => {
      try {
        setLoading(true);
        setError(null);

        const [docRes, discRes] = await Promise.all([
          directorService.getDocentes(),
          directorService.getDisciplinas(),
        ]);

        setDocentes(docRes.data || []);
        setDisciplinas(discRes.data || []);

        // Se vier docente selecionado, mantém fixo; senão seleciona o primeiro.
        if (!selectedDocenteId && (docRes.data || []).length > 0) {
          setDocenteId(String(docRes.data[0].id));
        }

        if ((discRes.data || []).length > 0) setDisciplinaId(String(discRes.data[0].id));
      } catch (e) {
        setError('Erro ao carregar docentes/disciplinas.');
        console.error(e);
      } finally {
        setLoading(false);
      }
    };

    load();
  }, []);

  const handleSubmit = async () => {

    if (!docenteId || !disciplinaId) {

        alert("Selecione um docente e uma disciplina.");

        return;

    }

    try {

        setSaving(true);

        await directorService.vincularDocenteDisciplina({

            docente_id: Number(docenteId),

            disciplina_id: Number(disciplinaId)

        });

        if (onSaved) {

            await onSaved();

        }

        alert("Docente vinculado com sucesso!");

        onClose();

    } catch (error) {

        console.error(error);

        alert(

            error?.response?.data?.message ||

            "Não foi possível realizar o vínculo."

        );

    } finally {

        setSaving(false);

    }

};

  return (
    <div className="modal-overlay">
      <div className="modal-content docente-disciplina-modal">
        <div className="modal-header">
          <h2>Vincular Docente à Disciplina</h2>
          <button className="close-button" onClick={onClose}>
            &times;
          </button>
        </div>

        <div className="modal-body">
          {loading && <p>Carregando...</p>}
          {error && <p className="error-text">{error}</p>}

          {!loading && !error && (
            <>
              <div className="field">
                <label>Docente</label>
                <select
                  value={docenteId}
                  onChange={(e) => setDocenteId(e.target.value)}
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

                  <span>

                      {docenteSelecionado?.nome || "Nenhum docente"}

                  </span>

                  <span className="arrow">

                      ➜

                  </span>

                  <span className="badge-disciplina">

                      {disciplinaSelecionada?.nome || "Nenhuma disciplina"}

                  </span>

              </div>


              <div className="field">
                <label>Disciplina</label>
                <select value={disciplinaId} onChange={(e) => setDisciplinaId(e.target.value)}>
                  {disciplinas.map((disc) => (
                    <option key={disc.id} value={disc.id}>
                      {disc.nome}
                    </option>
                  ))}
                </select>
              </div>
            </>
          )}
        </div>

        <div className="modal-footer">
          <button className="cancel-button" onClick={onClose}>
            Cancelar
          </button>
          <button className="confirm-button" onClick={handleSubmit} disabled={!docenteId || !disciplinaId || saving}>
            {saving ? "Salvando..." : "Vincular"}
          </button>
        </div>
      </div>
    </div>
  );
};

export default DocenteDisciplinaModal;
