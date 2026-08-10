import React from "react";
import "./ProfessorCard.css";

const ProfessorCard = ({
    professor,
    onOpenModal,
    onResetDisciplina,
}) => {
    const disciplina =
        professor.disciplina ||
        professor.disciplina_nome ||
        professor.nome_disciplina ||
        null;

    const temDisciplina = Boolean(disciplina && disciplina !== 'Sem disciplina' && disciplina !== 'Disciplina não informada');

    const handleReset = async (e) => {
        e.stopPropagation();
        if (!temDisciplina) return;

        if (window.confirm(`Tem certeza que deseja retirar a atribuição da disciplina "${disciplina}" do professor ${professor.nome}?`)) {
            try {
                await onResetDisciplina(professor.id);
            } catch (err) {
                console.error("Erro ao retirar atribuição:", err);
            }
        }
    };

    const handleCardClick = () => {
        onOpenModal(professor.id);
    };

    const handleKeyDown = (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            onOpenModal(professor.id);
        }
    };

    return (
        <div
            className={`professor-card ${temDisciplina ? 'has-disciplina' : 'no-disciplina'}`}
            onClick={handleCardClick}
            onKeyDown={handleKeyDown}
            role="button"
            tabIndex={0}
        >
            <div className="professor-card-header">
                <div className="professor-info">
                    <h3>{professor.nome}</h3>

                    {temDisciplina ? (
                        <div className="disciplina-badge">
                            <span className="disciplina-icon">📚</span>
                            <span className="disciplina-text">{disciplina}</span>
                        </div>
                    ) : (
                        <div className="sem-disciplina">
                            <span className="sem-disc-icon">⚠️</span>
                            <span>Nenhuma disciplina atribuída</span>
                        </div>
                    )}
                </div>
            </div>

            <div className="professor-card-actions" onClick={(e) => e.stopPropagation()}>
                {temDisciplina ? (
                    <>
                        <button
                            type="button"
                            className="btn-card-atribuir btn-alterar"
                            onClick={(e) => {
                                e.stopPropagation();
                                onOpenModal(professor.id);
                            }}
                            title="Alterar disciplina atribuída via modal"
                        >
                            ✏️ Alterar Disciplina
                        </button>
                        <button
                            type="button"
                            className="btn-card-reset"
                            onClick={handleReset}
                            title="Retirar atribuição desta disciplina do professor"
                        >
                            🔄 Retirar Atribuição
                        </button>
                    </>
                ) : (
                    <button
                        type="button"
                        className="btn-card-atribuir"
                        onClick={(e) => {
                            e.stopPropagation();
                            onOpenModal(professor.id);
                        }}
                        title="Abrir modal para atribuir disciplina a este professor"
                    >
                        ➕ Atribuir Disciplina
                    </button>
                )}
            </div>
        </div>
    );
};

export default ProfessorCard;