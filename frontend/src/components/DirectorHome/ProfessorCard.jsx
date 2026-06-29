import React from "react";
import "./ProfessorCard.css";

const ProfessorCard = ({ professor, isAlocado, onSelect }) => {

    const disciplina =
        professor.disciplina ||
        professor.disciplina_nome ||
        professor.nome_disciplina ||
        null;

    return (

        <div
            className={`professor-card 
                ${isAlocado ? "alocado" : ""}
                ${!isAlocado ? "clickable" : ""}
            `}
            onClick={onSelect}
        >

            <div className="professor-info">

                <h3>{professor.nome}</h3>

                {disciplina ? (

                    <div className="disciplina-badge">

                        {disciplina}

                    </div>

                ) : (

                    <div className="sem-disciplina">

                        Nenhuma disciplina vinculada

                    </div>

                )}

            </div>

            <div
                className={`professor-status 
                    ${isAlocado
                        ? "status-ocupado"
                        : "status-disponivel"}
                `}
            >

                {isAlocado
                    ? "Em aula"
                    : "Disponível"}

            </div>

            {isAlocado && (

                <div className="bloqueio-overlay">

                    <span className="bloqueio-icon">

                        🔒

                    </span>

                </div>

            )}

        </div>

    );

};

export default ProfessorCard;