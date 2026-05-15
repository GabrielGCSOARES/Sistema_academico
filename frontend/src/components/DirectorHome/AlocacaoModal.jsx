import React, { useState } from 'react';
import './AlocacaoModal.css';

const AlocacaoModal = ({ professor, salas, alocacoes, onClose, onAlocar }) => {
  const [selectedSala, setSelectedSala] = useState(null);

  const getSalaDisponibilidade = (salaId) => {
    return !alocacoes.some(aloc => aloc.sala_id === salaId);
  };

  const handleConfirmar = () => {
    if (selectedSala) {
      onAlocar(selectedSala);
    }
  };

  return (
    <div className="modal-overlay">
      <div className="modal-content">
        <div className="modal-header">
          <h2>Alocar Professor</h2>
          <button className="close-button" onClick={onClose}>&times;</button>
        </div>
        
        <div className="modal-body">
          <div className="professor-selected">
            <h3>Professor: {professor.nome}</h3>
            <p>Disciplina: {professor.disciplina}</p>
          </div>

          <div className="salas-selection">
            <h3>Selecione a sala:</h3>
            <div className="salas-options">
              {salas.map(sala => {
                const disponivel = getSalaDisponibilidade(sala.id);
                return (
                  <div
                    key={sala.id}
                    className={`sala-option ${!disponivel ? 'indisponivel' : ''} ${selectedSala === sala.id ? 'selected' : ''}`}
                    onClick={() => disponivel && setSelectedSala(sala.id)}
                  >
                    <div className="sala-option-header">
                      <h4>{sala.nome}</h4>
                      <span className={`disponibilidade-badge ${disponivel ? 'disponivel' : 'ocupado'}`}>
                        {disponivel ? 'Disponível' : 'Ocupada'}
                      </span>
                    </div>
                    <p>Capacidade: {sala.capacidade} alunos</p>
                    {!disponivel && (
                      <div className="sala-bloqueada">
                        <span>🚫 Sala já está em uso</span>
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          </div>
        </div>

        <div className="modal-footer">
          <button className="cancel-button" onClick={onClose}>
            Cancelar
          </button>
          <button 
            className="confirm-button"
            onClick={handleConfirmar}
            disabled={!selectedSala}
          >
            Confirmar Alocação
          </button>
        </div>
      </div>
    </div>
  );
};

export default AlocacaoModal;