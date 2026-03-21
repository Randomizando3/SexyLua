(() => {
    const config = window.SexyLuaPrototype || null;

    if (!config) {
        return;
    }

    const normalize = (value) => String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/\s+/g, ' ')
        .trim()
        .toLowerCase();

    const textOf = (element) => normalize(element && element.textContent);
    const routes = config.routes || {};
    const moderationIds = (config.moderation && config.moderation.contentIds) || [];
    const currentPage = config.page || '';

    const navigate = (url) => {
        if (url) {
            window.location.href = url;
        }
    };

    const bindAction = (element, handler) => {
        if (!element || element.dataset.prototypeBound === '1') {
            return;
        }

        element.dataset.prototypeBound = '1';
        element.addEventListener('click', (event) => {
            event.preventDefault();
            handler(event);
        });
    };

    const submitForm = (id, beforeSubmit) => {
        const form = document.getElementById(id);

        if (!form) {
            return false;
        }

        if (typeof beforeSubmit === 'function') {
            beforeSubmit(form);
        }

        form.submit();

        return true;
    };

    const setValue = (form, name, value) => {
        const field = form.querySelector(`[name="${name}"]`);

        if (field) {
            field.value = value;
        }
    };

    const requireSubscriber = () => {
        if (!config.auth || config.role !== 'subscriber') {
            navigate(routes.login);
            return false;
        }

        return true;
    };

    const requireCreator = () => {
        if (!config.auth || config.role !== 'creator') {
            navigate(routes.login);
            return false;
        }

        return true;
    };

    const requireAdmin = () => {
        if (!config.auth || config.role !== 'admin') {
            navigate(routes.login);
            return false;
        }

        return true;
    };

    const toMysqlDatetime = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');

        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    };

    const routeByLabel = (label) => {
        switch (label) {
            case 'live cam':
            case 'ao vivo':
                return routes.live;
            case 'explorar':
            case 'explore':
            case 'descobrir':
            case 'criadores':
            case 'ver todas':
            case 'ver todos':
            case 'ver ranking completo':
                return routes.explore;
            case 'mensagens':
                return config.auth && config.role === 'subscriber' ? routes.subscriberMessages : routes.login;
            case 'assinaturas':
            case 'minhas assinaturas':
                if (!config.auth) {
                    return routes.login;
                }

                if (config.role === 'subscriber') {
                    return routes.subscriberSubscriptions;
                }

                if (config.role === 'creator') {
                    return routes.creatorMemberships;
                }

                return routes.home;
            case 'favoritos':
                return config.auth && config.role === 'subscriber' ? routes.subscriberFavorites : routes.login;
            case 'carteira':
            case 'minha carteira':
            case 'ganhos estelares':
                if (!config.auth) {
                    return routes.login;
                }

                if (config.role === 'creator') {
                    return routes.creatorWallet;
                }

                if (config.role === 'admin') {
                    return routes.adminFinance;
                }

                return routes.subscriberWallet;
            case 'conteudo':
            case 'meu conteudo':
                if (!config.auth) {
                    return routes.login;
                }

                return config.role === 'admin' ? routes.adminModeration : routes.creatorContent;
            case 'transmissao ao vivo':
            case 'live stream':
            case 'configuracao ao vivo':
            case 'configurar live':
                return config.auth && config.role === 'creator' ? routes.creatorLive : routes.live;
            case 'assinantes':
            case 'subscribers':
                return config.auth && config.role === 'creator' ? routes.creatorMemberships : routes.login;
            case 'metricas lunares':
            case 'painel':
            case 'dashboard':
            case 'inicio':
            case 'home':
                if (config.auth && config.role === 'subscriber') {
                    return routes.subscriber;
                }

                if (config.auth && config.role === 'creator') {
                    return routes.creator;
                }

                if (config.auth && config.role === 'admin') {
                    return routes.admin;
                }

                return routes.home;
            case 'usuarios':
            case 'gestao de usuarios':
                return config.auth && config.role === 'admin' ? routes.adminUsers : routes.login;
            case 'relatorios':
            case 'estatisticas':
            case 'financeiro':
            case 'pagamentos':
                return config.auth && config.role === 'admin' ? routes.adminFinance : routes.login;
            case 'configuracoes':
            case 'settings':
            case 'seguranca':
                if (config.auth && config.role === 'admin') {
                    return routes.adminSettings;
                }

                if (config.auth && config.role === 'creator') {
                    return routes.creatorLive;
                }

                return routes.home;
            case 'go live':
            case 'entrar ao vivo':
                return config.auth && config.role === 'creator' ? routes.creatorLive : routes.live;
            case 'quero participar':
                return routes.register;
            default:
                return null;
        }
    };

    document.querySelectorAll('a[href="#"], button').forEach((element) => {
        const label = textOf(element);

        if (!label) {
            return;
        }

        if (label === 'login') {
            bindAction(element, () => navigate(routes.login));
            return;
        }

        if (label === 'registro') {
            bindAction(element, () => navigate(routes.register));
            return;
        }

        if (label === 'sair') {
            bindAction(element, () => submitForm('prototype-logout-form'));
            return;
        }

        if (label === 'seguir') {
            bindAction(element, () => {
                if (!requireSubscriber()) {
                    return;
                }

                submitForm('prototype-favorite-form');
            });
            return;
        }

        if (label === 'enviar mensagem') {
            bindAction(element, () => {
                if (!requireSubscriber()) {
                    return;
                }

                submitForm('prototype-message-form');
            });
            return;
        }

        if (label === 'dar gorjeta' || label === 'gorjeta') {
            bindAction(element, () => {
                if (!requireSubscriber()) {
                    return;
                }

                submitForm('prototype-tip-form');
            });
            return;
        }

        if (label === 'recarregar') {
            bindAction(element, () => {
                if (!requireSubscriber()) {
                    return;
                }

                submitForm('prototype-topup-form');
            });
            return;
        }

        if (label === 'sacar fundos') {
            bindAction(element, () => {
                if (!requireCreator()) {
                    return;
                }

                submitForm('prototype-payout-form');
            });
            return;
        }

        if (label === 'salvar alteracoes') {
            bindAction(element, () => {
                if (!requireAdmin()) {
                    return;
                }

                submitForm('prototype-admin-settings-form', (form) => {
                    const numberInput = document.querySelector('input[placeholder="20"]');
                    const rangeInputs = document.querySelectorAll('input[type="range"]');
                    const slowInput = document.querySelector('input[value$="s"]');
                    const toggles = document.querySelectorAll('input[type="checkbox"]');

                    setValue(form, 'platform_fee_percent', numberInput && numberInput.value ? numberInput.value : '20');
                    setValue(form, 'withdraw_min_tokens', rangeInputs[0] ? rangeInputs[0].value : '50');
                    setValue(form, 'withdraw_max_tokens', rangeInputs[1] ? rangeInputs[1].value : '25000');
                    setValue(form, 'slow_mode_seconds', slowInput ? String(parseInt(slowInput.value, 10) || 0) : '0');
                    setValue(form, 'maintenance_mode', toggles[0] && toggles[0].checked ? '1' : '0');
                    setValue(form, 'auto_moderation', toggles[2] && toggles[2].checked ? '1' : '0');
                    setValue(form, 'live_chat_enabled', toggles[3] && toggles[3].checked ? '1' : '0');
                });
            });
            return;
        }

        if (label === 'descartar') {
            bindAction(element, () => window.location.reload());
            return;
        }

        if (label === 'salvar pre-definicao') {
            bindAction(element, () => {
                if (!requireCreator()) {
                    return;
                }

                submitForm('prototype-creator-live-form', (form) => {
                    const titleInput = document.querySelector('input[placeholder*="Noite de Gala"]');
                    const priceInput = document.querySelector('input[type="number"][placeholder="150"]');

                    setValue(form, 'title', titleInput && titleInput.value ? titleInput.value : 'Live criada do painel');
                    setValue(form, 'description', 'Live criada a partir do dashboard do criador.');
                    setValue(form, 'price_tokens', priceInput && priceInput.value ? priceInput.value : '0');
                    setValue(form, 'scheduled_for', toMysqlDatetime(new Date(Date.now() + 86400000)));
                    setValue(form, 'status', 'scheduled');
                });
            });
            return;
        }

        if (label === 'novo conteudo') {
            bindAction(element, () => {
                if (!requireCreator()) {
                    return;
                }

                submitForm('prototype-creator-content-form', (form) => {
                    const stamp = new Date().toLocaleString('pt-BR');

                    setValue(form, 'title', `Novo conteudo do studio ${stamp}`);
                    setValue(form, 'excerpt', 'Conteudo criado a partir do prototipo visual.');
                    setValue(form, 'body', 'Rascunho criado pela integracao do backend sobre o layout original.');
                    setValue(form, 'kind', 'gallery');
                    setValue(form, 'visibility', 'subscriber');
                    setValue(form, 'status', 'pending');
                });
            });
            return;
        }

        if (label === 'iniciar transmissao agora') {
            bindAction(element, () => {
                if (!requireCreator()) {
                    return;
                }

                submitForm('prototype-creator-live-form', (form) => {
                    const titleInput = document.querySelector('input[placeholder*="Uma Noite sob a Lua Cheia"]');
                    const priceInput = document.querySelector('input[type="number"][placeholder="0"]');
                    const categorySelect = document.querySelector('select');
                    const privacyOptions = Array.from(document.querySelectorAll('input[name="privacy"]'));
                    const privateMode = privacyOptions[1] && privacyOptions[1].checked;
                    const category = categorySelect ? categorySelect.value : 'Chatting & Chill';

                    setValue(form, 'title', titleInput && titleInput.value ? titleInput.value : 'Nova live SexyLua');
                    setValue(form, 'description', `Live criada pelo Creator Studio em ${category}${privateMode ? ' para inscritos' : ' com acesso publico'}.`);
                    setValue(form, 'price_tokens', priceInput && priceInput.value ? priceInput.value : '0');
                    setValue(form, 'scheduled_for', toMysqlDatetime(new Date()));
                    setValue(form, 'status', 'live');
                    setValue(form, 'chat_enabled', '1');
                });
            });
            return;
        }

        if (label === 'assinar agora' || label === 'assine para ver' || label.startsWith('desbloquear')) {
            bindAction(element, () => {
                if (!requireSubscriber()) {
                    return;
                }

                submitForm('prototype-subscribe-form');
            });
            return;
        }

        const route = routeByLabel(label);

        if (route) {
            bindAction(element, () => navigate(route));
        }
    });

    const approveButtons = Array.from(document.querySelectorAll('button')).filter((button) => textOf(button) === 'aprovar');
    approveButtons.forEach((button, index) => {
        bindAction(button, () => {
            if (!requireAdmin()) {
                return;
            }

            const contentId = moderationIds[index];

            if (!contentId) {
                return;
            }

            submitForm('prototype-admin-review-form', (form) => {
                setValue(form, 'content_id', String(contentId));
                setValue(form, 'decision', 'approved');
                setValue(form, 'moderation_feedback', 'Conteudo aprovado pelo fluxo do prototipo.');
            });
        });
    });

    const removeButtons = Array.from(document.querySelectorAll('button')).filter((button) => textOf(button) === 'remover');
    removeButtons.forEach((button, index) => {
        bindAction(button, () => {
            if (!requireAdmin()) {
                return;
            }

            const contentId = moderationIds[index];

            if (!contentId) {
                return;
            }

            submitForm('prototype-admin-review-form', (form) => {
                setValue(form, 'content_id', String(contentId));
                setValue(form, 'decision', 'rejected');
                setValue(form, 'moderation_feedback', 'Conteudo removido pelo fluxo do prototipo.');
            });
        });
    });

    const banButton = Array.from(document.querySelectorAll('button')).find((button) => textOf(button) === 'banir conta imediatamente');
    if (banButton) {
        bindAction(banButton, () => {
            if (!requireAdmin()) {
                return;
            }

            const contentId = moderationIds[0];

            if (!contentId) {
                return;
            }

            submitForm('prototype-admin-review-form', (form) => {
                setValue(form, 'content_id', String(contentId));
                setValue(form, 'decision', 'rejected');
                setValue(form, 'moderation_feedback', 'Conta e conteudo sinalizados para acao administrativa imediata.');
            });
        });
    }

    const subscriberMessageInput = document.querySelector('input[placeholder*="Escreva uma mensagem"]');
    if (subscriberMessageInput) {
        const sendButton = subscriberMessageInput.parentElement && subscriberMessageInput.parentElement.nextElementSibling
            ? subscriberMessageInput.parentElement.nextElementSibling
            : null;

        if (sendButton && sendButton.tagName === 'BUTTON') {
            bindAction(sendButton, () => {
                if (!requireSubscriber()) {
                    return;
                }

                submitForm('prototype-subscriber-message-form', (form) => {
                    setValue(form, 'body', subscriberMessageInput.value || 'Mensagem enviada pelo layout original.');
                });
            });
        }
    }

    const liveChatInput = document.querySelector('input[placeholder*="Diga algo sensual"]');
    if (liveChatInput) {
        const sendButton = liveChatInput.parentElement ? liveChatInput.parentElement.querySelector('button:last-of-type') : null;

        if (sendButton) {
            bindAction(sendButton, () => {
                if (!config.auth) {
                    navigate(routes.login);
                    return;
                }

                submitForm('prototype-live-chat-form', (form) => {
                    setValue(form, 'body', liveChatInput.value || 'Mensagem enviada da live.');
                });
            });
        }
    }

    const adminUsersSearch = document.querySelector('input[placeholder*="nome, email ou ID"]');
    if (adminUsersSearch) {
        adminUsersSearch.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();
            navigate(`/admin/users?q=${encodeURIComponent(adminUsersSearch.value || '')}`);
        });
    }

    document.querySelectorAll('[data-prototype-flash-close]').forEach((button) => {
        button.addEventListener('click', () => {
            const parent = button.closest('[data-prototype-flash]');
            if (parent) {
                parent.remove();
            }
        });
    });

    document.querySelectorAll('[data-prototype-flash]').forEach((flash, index) => {
        window.setTimeout(() => {
            flash.remove();
        }, 4200 + (index * 500));
    });

    if (currentPage === 'admin.settings') {
        const priceInput = document.querySelector('input[placeholder*="R$"]');

        if (priceInput && !priceInput.value) {
            priceInput.value = priceInput.getAttribute('placeholder') || '';
        }
    }
})();
