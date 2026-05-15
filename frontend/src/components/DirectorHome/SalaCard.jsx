import React from 'react';
import './SalaCard.css';

const SalaCard = ({ sala, status, onDesalocar }) => {
  return (
    <div className={`sala-card ${status.ocupada ? 'ocupada' : 'livre'}`}>
      <div className="sala-header">
        <h3>{sala.nome}</h3>
        <span className="sala-capacidade">Capacidade: {sala.capacidade}</span>
      </div>
      
      <div className="sala-content">
        {status.ocupada ? (
          <>
            <div className="sala-status">
              <span className="status-indicator ocupado"></span>
              <span className="status-text">Ocupada</span>
            </div>
            <p className="professor-na-sala">{status.professor}</p>
            <button 
              className="desalocar-button"
              onClick={onDesalocar}
            >
              Remanejar Professor
            </button>
            <div className="bloqueio-visual">
              <span>🚫 Sala em uso</span>
            </div>
          </>
        ) : (
          <>
            <div className="sala-status">
              <span className="status-indicator livre"></span>
              <span className="status-text">Disponível</span>
            </div>
            <p className="sala-disponivel">Pronta para uso</p>
          </>
        )}
      </div>
    </div>
  );
};

export default SalaCard;