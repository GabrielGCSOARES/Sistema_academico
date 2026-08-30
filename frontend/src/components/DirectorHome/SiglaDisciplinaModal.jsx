import React, { useState, useEffect, useMemo, useCallback } from 'react';
import './SiglaDisciplinaModal.css';
import { directorService } from '../../services/api';

/**
 * SiglaDisciplinaModal - Modal para gerenciamento e inserção de siglas nas disciplinas.
 * 
 * Regras:
 * - Permite inserir até 2 siglas por disciplina (ex: Diurno e Noturno).
 * - Permite redefinir/limpar as siglas caso tenham sido digitadas incorretamente.
 * - Disciplinas cadastradas no modal de professor aparecem aqui automaticamente sem sigla (pendentes).
 * - A inserção da sigla NÃO bloqueia a disciplina para alocação/vínculo.
 */
const SiglaDisciplinaModal = ({ onClose, onSaved }) => {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const [disciplinas, setDisciplinas] = useState([]);
  const [docentes, setDocentes] = useState([]);

  // Filtros simplificados: apenas busca por texto e status (todas / pendentes / com sigla)
  const [busca, setBusca] = useState('');
  const [filtroStatus, setFiltroStatus] = useState('todos'); // 'todos', 'sem_sigla', 'com_sigla'

  // Estado de edição de siglas: { [id]: { sigla: string, sigla_secundaria: string } }
  const [siglasEditadas, setSiglasEditadas] = useState({});
  const [salvandoId, setSalvandoId] = useState(null);
  const [redefinindoId, setRedefinindoId] = useState(null);
  const [sucessoId, setSucessoId] = useState(null);

  const carregarDados = useCallback(async () => {
    try {
      setLoading(true);
      setError(null);

      const [discRes, docRes] = await Promise.all([
        directorService.getDisciplinas(),
        directorService.getDocentes(),
      ]);

      const listaDisciplinas = discRes.data || [];
      setDisciplinas(listaDisciplinas);
      setDocentes(docRes.data || []);

      // Inicializa o mapa com as duas siglas de cada disciplina
      const mapa = {};
      listaDisciplinas.forEach((d) => {
        mapa[d.id] = {
          sigla: d.sigla || '',
          sigla_secundaria: d.sigla_secundaria || '',
        };
      });
      setSiglasEditadas(mapa);
    } catch (err) {
      setError('Erro ao carregar disciplinas. Verifique a conexão com o servidor.');
      console.error(err);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    carregarDados();
  }, [carregarDados]);

  // Mapa de docentes vinculados por disciplina_id
  const docentesPorDisciplina = useMemo(() => {
    const mapa = {};
    docentes.forEach((doc) => {
      if (doc.disciplina_id) {
        mapa[doc.disciplina_id] = doc.nome;
      }
    });
    return mapa;
  }, [docentes]);

  // Filtragem
  const disciplinasFiltradas = useMemo(() => {
    const termo = busca.trim().toLowerCase();

    return disciplinas.filter((disc) => {
      const sigla1 = (siglasEditadas[disc.id]?.sigla || disc.sigla || '').toLowerCase();
      const sigla2 = (siglasEditadas[disc.id]?.sigla_secundaria || disc.sigla_secundaria || '').toLowerCase();

      // Busca por nome ou qualquer uma das siglas
      if (termo) {
        const nomeMatch = disc.nome.toLowerCase().includes(termo);
        const sigla1Match = sigla1.includes(termo);
        const sigla2Match = sigla2.includes(termo);
        if (!nomeMatch && !sigla1Match && !sigla2Match) {
          return false;
        }
      }

      // Filtro por status de sigla
      const temSigla = Boolean((disc.sigla && disc.sigla.trim()) || (disc.sigla_secundaria && disc.sigla_secundaria.trim()));
      if (filtroStatus === 'com_sigla' && !temSigla) return false;
      if (filtroStatus === 'sem_sigla' && temSigla) return false;

      return true;
    });
  }, [disciplinas, busca, filtroStatus, siglasEditadas]);

  // Totalizadores
  const totalSemSigla = useMemo(() => {
    return disciplinas.filter((d) => !d.sigla && !d.sigla_secundaria).length;
  }, [disciplinas]);

  // Salvar siglas (até 2) de uma disciplina
  const handleSalvarSiglas = async (disciplinaId) => {
    const dados = siglasEditadas[disciplinaId] || {};
    const sigla1 = (dados.sigla || '').trim().toUpperCase();
    const sigla2 = (dados.sigla_secundaria || '').trim().toUpperCase();

    setSalvandoId(disciplinaId);
    try {
      const response = await directorService.atualizarSiglaDisciplina(
        disciplinaId,
        sigla1 || null,
        sigla2 || null
      );

      const discAtualizada = response.data.disciplina;

      setDisciplinas((prev) =>
        prev.map((d) => (d.id === disciplinaId ? discAtualizada : d))
      );

      setSiglasEditadas((prev) => ({
        ...prev,
        [disciplinaId]: {
          sigla: discAtualizada.sigla || '',
          sigla_secundaria: discAtualizada.sigla_secundaria || '',
        },
      }));

      setSucessoId(disciplinaId);
      setTimeout(() => {
        setSucessoId((curr) => (curr === disciplinaId ? null : curr));
      }, 2500);

      if (onSaved) {
        onSaved();
      }
    } catch (err) {
      console.error(err);
      alert(err?.response?.data?.message || 'Erro ao salvar siglas da disciplina.');
    } finally {
      setSalvandoId(null);
    }
  };

  // Redefinir (limpar) siglas de uma disciplina
  const handleRedefinirSiglas = async (disciplinaId) => {
    if (!window.confirm('Deseja realmente redefinir e limpar as siglas desta disciplina?')) {
      return;
    }

    setRedefinindoId(disciplinaId);
    try {
      const response = await directorService.redefinirSiglasDisciplina(disciplinaId);
      const discAtualizada = response.data.disciplina;

      setDisciplinas((prev) =>
        prev.map((d) => (d.id === disciplinaId ? discAtualizada : d))
      );

      setSiglasEditadas((prev) => ({
        ...prev,
        [disciplinaId]: {
          sigla: '',
          sigla_secundaria: '',
        },
      }));

      setSucessoId(disciplinaId);
      setTimeout(() => {
        setSucessoId((curr) => (curr === disciplinaId ? null : curr));
      }, 2500);

      if (onSaved) {
        onSaved();
      }
    } catch (err) {
      console.error(err);
      alert(err?.response?.data?.message || 'Erro ao redefinir siglas.');
    } finally {
      setRedefinindoId(null);
    }
  };

  return (
    <div className="sigla-modal-overlay" role="presentation" onClick={onClose}>
      <div
        className="sigla-modal-content"
        role="dialog"
        aria-modal="true"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Header no padrão de cores do sistema */}
        <header className="sigla-modal-header">
          <div className="sigla-header-title">
            <div>
              <h2>Siglas das Disciplinas</h2>
              <span className="sigla-header-subtitle">
                Atribua até 2 siglas (ex: Diurno e Noturno) para diferenciar matérias por turno
              </span>
            </div>
          </div>
          <button type="button" className="sigla-modal-close" onClick={onClose} aria-label="Fechar">
            &times;
          </button>
        </header>

        {/* Toolbar: Busca + Filtro de Status + Totalizador */}
        <div className="sigla-toolbar-clean">
          <div className="search-box">
            <span className="search-icon">🔍</span>
            <input
              type="text"
              placeholder="Buscar por nome da disciplina ou sigla..."
              value={busca}
              onChange={(e) => setBusca(e.target.value)}
              autoFocus
            />
            {busca && (
              <button type="button" className="btn-clear-search" onClick={() => setBusca('')}>
                &times;
              </button>
            )}
          </div>

          <div className="status-tabs">
            <button
              type="button"
              className={`status-tab ${filtroStatus === 'todos' ? 'active' : ''}`}
              onClick={() => setFiltroStatus('todos')}
            >
              Todas ({disciplinas.length})
            </button>
            <button
              type="button"
              className={`status-tab ${filtroStatus === 'sem_sigla' ? 'active' : ''}`}
              onClick={() => setFiltroStatus('sem_sigla')}
            >
              ⏳ Pendentes sem Sigla ({totalSemSigla})
            </button>
            <button
              type="button"
              className={`status-tab ${filtroStatus === 'com_sigla' ? 'active' : ''}`}
              onClick={() => setFiltroStatus('com_sigla')}
            >
              ✓ Com Sigla ({disciplinas.length - totalSemSigla})
            </button>
          </div>
        </div>

        {/* Container com scroll vertical perfeito */}
        <div className="sigla-modal-body-scrollable">
          {loading && (
            <div className="sigla-state-box loading">
              <div className="sigla-spinner"></div>
              <p>Carregando disciplinas...</p>
            </div>
          )}

          {error && (
            <div className="sigla-state-box error">
              <p>{error}</p>
              <button type="button" onClick={carregarDados} className="btn-retry">
                Tentar novamente
              </button>
            </div>
          )}

          {!loading && !error && (
            <div className="disciplinas-table-wrapper">
              <table className="disciplinas-sigla-table">
                <thead>
                  <tr>
                    <th style={{ width: '42%' }}>Disciplina</th>
                    <th style={{ width: '22%' }}>Sigla 1 (Matutino/Vespertino)</th>
                    <th style={{ width: '22%' }}>Sigla 2 (Noturno)</th>
                    <th style={{ width: '14%' }}>Ações</th>
                  </tr>
                </thead>
                <tbody>
                  {disciplinasFiltradas.length === 0 ? (
                    <tr>
                      <td colSpan="4" className="empty-cell">
                        Nenhuma disciplina encontrada para esta busca/filtro.
                      </td>
                    </tr>
                  ) : (
                    disciplinasFiltradas.map((disc) => {
                      const sigla1Valor = siglasEditadas[disc.id]?.sigla ?? '';
                      const sigla2Valor = siglasEditadas[disc.id]?.sigla_secundaria ?? '';
                      const original1 = disc.sigla || '';
                      const original2 = disc.sigla_secundaria || '';
                      const foiAlterado =
                        sigla1Valor.trim().toUpperCase() !== original1.trim().toUpperCase() ||
                        sigla2Valor.trim().toUpperCase() !== original2.trim().toUpperCase();

                      const temAlgumaSigla = Boolean(original1 || original2);
                      const isSalvando = salvandoId === disc.id;
                      const isRedefinindo = redefinindoId === disc.id;
                      const isSucesso = sucessoId === disc.id;
                      const profAtribuido = docentesPorDisciplina[disc.id];

                      return (
                        <tr key={disc.id} className={isSucesso ? 'row-success' : ''}>
                          {/* Nome da Matéria */}
                          <td>
                            <div className="nome-disciplina-box">
                              <span className="disc-nome-text">{disc.nome}</span>
                              <div className="disc-tags-row">
                                {profAtribuido ? (
                                  <span className="badge-prof">👨‍🏫 {profAtribuido}</span>
                                ) : (
                                  <span className="badge-livre">✓ Livre</span>
                                )}
                                {!temAlgumaSigla && (
                                  <span className="badge-pendente">⏳ Sem Sigla</span>
                                )}
                              </div>
                            </div>
                          </td>

                          {/* Sigla 1 */}
                          <td>
                            <input
                              type="text"
                              className={`sigla-input ${foiAlterado ? 'modified' : ''}`}
                              placeholder="Ex: AUT-M"
                              value={sigla1Valor}
                              onChange={(e) => {
                                const val = e.target.value.toUpperCase();
                                setSiglasEditadas((prev) => ({
                                  ...prev,
                                  [disc.id]: {
                                    ...(prev[disc.id] || {}),
                                    sigla: val,
                                  },
                                }));
                              }}
                              onKeyDown={(e) => {
                                if (e.key === 'Enter') {
                                  e.preventDefault();
                                  handleSalvarSiglas(disc.id);
                                }
                              }}
                              maxLength={20}
                            />
                          </td>

                          {/* Sigla 2 */}
                          <td>
                            <input
                              type="text"
                              className={`sigla-input ${foiAlterado ? 'modified' : ''}`}
                              placeholder="Ex: AUT-N"
                              value={sigla2Valor}
                              onChange={(e) => {
                                const val = e.target.value.toUpperCase();
                                setSiglasEditadas((prev) => ({
                                  ...prev,
                                  [disc.id]: {
                                    ...(prev[disc.id] || {}),
                                    sigla_secundaria: val,
                                  },
                                }));
                              }}
                              onKeyDown={(e) => {
                                if (e.key === 'Enter') {
                                  e.preventDefault();
                                  handleSalvarSiglas(disc.id);
                                }
                              }}
                              maxLength={20}
                            />
                          </td>

                          {/* Ações (Salvar / Redefinir) */}
                          <td>
                            <div className="action-buttons-group">
                              <button
                                type="button"
                                className={`btn-save-sigla ${isSucesso ? 'btn-saved' : ''} ${foiAlterado ? 'btn-highlight' : ''}`}
                                onClick={() => handleSalvarSiglas(disc.id)}
                                disabled={isSalvando || isRedefinindo}
                                title="Salvar sigla(s)"
                              >
                                {isSalvando ? '...' : isSucesso ? '✓' : 'Salvar'}
                              </button>

                              {temAlgumaSigla && (
                                <button
                                  type="button"
                                  className="btn-reset-sigla"
                                  onClick={() => handleRedefinirSiglas(disc.id)}
                                  disabled={isSalvando || isRedefinindo}
                                  title="Redefinir / Limpar siglas"
                                >
                                  {isRedefinindo ? '...' : '↺'}
                                </button>
                              )}
                            </div>
                          </td>
                        </tr>
                      );
                    })
                  )}
                </tbody>
              </table>
            </div>
          )}
        </div>

        {/* Footer */}
        <footer className="sigla-modal-footer">
          <div className="footer-left-info">
            <span>Mostrando <strong>{disciplinasFiltradas.length}</strong> de <strong>{disciplinas.length}</strong> disciplinas</span>
          </div>
          <button type="button" className="btn-close-sigla-modal" onClick={onClose}>
            Concluir / Fechar
          </button>
        </footer>
      </div>
    </div>
  );
};

export default SiglaDisciplinaModal;
