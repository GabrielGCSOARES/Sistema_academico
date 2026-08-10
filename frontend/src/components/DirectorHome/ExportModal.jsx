import React, { useState } from 'react';
import './ExportModal.css';

const ExportModal = ({ onClose, onExportProfessor, professores, relatorioProfessorId, setRelatorioProfessorId }) => {
  const [modo, setModo] = useState(null); // 'professor' | 'grade'
  const [exporting, setExporting] = useState(false);

  const handleExportProfessor = async () => {
    setExporting(true);
    try { await onExportProfessor(); } finally { setExporting(false); }
    onClose();
  };

  const handleExportGrade = async () => {
    setExporting(true);
    try {
      const url = `${process.env.REACT_APP_API_URL || 'http://localhost:8000/api'}/alocacoes/exportar-xlsx`;
      const resp = await fetch(url);
      if (!resp.ok) throw new Error('Erro ao exportar');
      const blob = await resp.blob();
      const objUrl = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = objUrl;
      link.download = `grade-semana-completa.xlsx`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(objUrl);
    } catch (err) {
      alert('Erro ao exportar a grade da semana completa.');
    } finally {
      setExporting(false);
    }
    onClose();
  };

  return (
    <div className="export-modal-overlay" onClick={onClose}>
      <div className="export-modal" onClick={e => e.stopPropagation()}>
        <button className="export-modal-close" onClick={onClose} aria-label="Fechar">✕</button>

        <div className="export-modal-header">
          <span className="export-modal-icon">📊</span>
          <h2>Exportar XLSX</h2>
          <p>Escolha o tipo de exportação desejado</p>
        </div>

        {!modo && (
          <div className="export-mode-cards">
            <button className="export-mode-card" onClick={() => setModo('professor')}>
              <span className="mode-card-icon">👤</span>
              <strong>Por Professor</strong>
              <span>Exporta as alocações da semana filtradas por professor</span>
            </button>
            <button className="export-mode-card" onClick={() => setModo('grade')}>
              <span className="mode-card-icon">🗓️</span>
              <strong>Semana Completa</strong>
              <span>Exporta a grade da semana completa com todos os professores e horários</span>
            </button>
          </div>
        )}

        {modo === 'professor' && (
          <div className="export-form">
            <button className="export-back-btn" onClick={() => setModo(null)}>← Voltar</button>
            <h3>Exportar por Professor</h3>

            <label className="export-field">
              <span>Filtrar professor</span>
              <select
                value={relatorioProfessorId}
                onChange={e => setRelatorioProfessorId(e.target.value)}
              >
                <option value="">Todos os professores</option>
                {professores.map(p => (
                  <option key={p.id} value={p.id}>{p.nome}</option>
                ))}
              </select>
            </label>

            <p className="export-hint">
              Exporta alocações da semana atual{relatorioProfessorId ? ' do professor selecionado' : ' de todos os professores'}.
            </p>

            <button
              className="export-confirm-btn"
              onClick={handleExportProfessor}
              disabled={exporting}
            >
              {exporting ? '⏳ Exportando...' : '⬇ Baixar XLSX'}
            </button>
          </div>
        )}

        {modo === 'grade' && (
          <div className="export-form">
            <button className="export-back-btn" onClick={() => setModo(null)}>← Voltar</button>
            <h3>Exportar Grade da Semana Completa</h3>

            <p className="export-hint">
              Exporta a grade completa da semana (Segunda a Sexta) com <strong>todos os professores</strong> e <strong>todos os horários</strong>.
            </p>

            <button
              className="export-confirm-btn"
              onClick={handleExportGrade}
              disabled={exporting}
            >
              {exporting ? '⏳ Exportando...' : '⬇ Baixar XLSX'}
            </button>
          </div>
        )}
      </div>
    </div>
  );
};

export default ExportModal;

