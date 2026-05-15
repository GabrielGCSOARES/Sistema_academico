import React from 'react';
import './ProfessorCard.css';

const ProfessorCard = ({ professor, isAlocado, onSelect }) => {
  return (
    <div 
      className={`professor-card ${isAlocado ? 'alocado' : ''} ${!isAlocado ? 'clickable' : ''}`}
      onClick={onSelect}
    >
      <div className="professor-info">
        <h3>{professor.nome}</h3>
        <p className="professor-disciplina">{professor.disciplina}</p>
      </div>
      <div className={`professor-status ${isAlocado ? 'status-ocupado' : 'status-disponivel'}`}>
        {isAlocado ? 'Em aula' : 'Disponível'}
      </div>
      {isAlocado && (
        <div className="bloqueio-overlay">
          <span className="bloqueio-icon">🔒</span>
        </div>
      )}
    </div>
  );
};

export default ProfessorCard;