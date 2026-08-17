import React from "react";
import "./ProfessorCard.css";

const ProfessorCard = ({
    professor,
    onOpenModal,
    onResetDisciplina,
    onEditProfessor,
    onDeleteProfessor,
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

    const handleEdit = (e) => {
        e.stopPropagation();
        if (onEditProfessor) {
            onEditProfessor(professor);
        }
    };

    const handleDelete = async (e) => {
        e.stopPropagation();
        if (window.confirm(`Tem certeza que deseja excluir o professor "${professor.nome}"?\n\nTodas as alocações deste professor também serão removidas do sistema.`)) {
            try {
                await onDeleteProfessor(professor.id);
            } catch (err) {
                console.error("Erro ao excluir professor:", err);
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

                <div className="professor-card-top-actions" onClick={(e) => e.stopPropagation()}>
                    <button
                        type="button"
                        className="btn-card-icon btn-card-edit"
                        onClick={handleEdit}
                        title="Editar nome do professor"
                        aria-label="Editar nome"
                    >
                        ✏️
                    </button>
                    <button
                        type="button"
                        className="btn-card-icon btn-card-delete"
                        onClick={handleDelete}
                        title="Excluir professor"
                        aria-label="Excluir professor"
                    >
                        🗑️
                    </button>
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
                            📚 Alterar Disciplina
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