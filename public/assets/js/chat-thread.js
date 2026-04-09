(function () {
    if (window.SexyLuaChatComposer) {
        return;
    }

    const DEFAULT_FILE_LABEL = "Imagem, video, documento ou pacote privado.";
    const LUACOIN_ICON = "/assets/img/luacoin.png";

    function escapeHtml(value) {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function nl2br(value) {
        return escapeHtml(value).replace(/\n/g, "<br>");
    }

    function findComposeParts(form) {
        return {
            submitButton: form.querySelector("[data-chat-submit]"),
            textarea: form.querySelector("textarea[name='body']"),
            thread: form.closest("[data-mobile-chat-panel]")?.querySelector("[data-chat-thread]"),
            threadItems: form.closest("[data-mobile-chat-panel]")?.querySelector("[data-chat-thread-items]"),
            feedback: form.querySelector("[data-chat-feedback]"),
            fileInput: form.querySelector("input[type='file'][name='attachment_file']"),
            details: form.querySelector("[data-chat-compose-details]"),
            conversationId: form.querySelector("input[name='conversation_id']")?.value ?? "",
            role: form.getAttribute("data-chat-role") || "subscriber",
        };
    }

    function setFeedback(container, message, type) {
        if (!container) {
            return;
        }

        if (!message) {
            container.classList.add("hidden");
            container.textContent = "";
            container.className = "mt-3 hidden rounded-2xl bg-surface-container-low px-4 py-3 text-sm text-on-surface-variant";
            return;
        }

        container.classList.remove("hidden");
        container.textContent = message;
        container.className = "mt-3 rounded-2xl px-4 py-3 text-sm";

        if (type === "error") {
            container.classList.add("bg-rose-50", "text-rose-700");
            return;
        }

        if (type === "success") {
            container.classList.add("bg-emerald-50", "text-emerald-700");
            return;
        }

        container.classList.add("bg-surface-container-low", "text-on-surface-variant");
    }

    function setSubmitting(button, isSubmitting) {
        if (!button) {
            return;
        }

        button.disabled = isSubmitting;
        button.classList.toggle("opacity-70", isSubmitting);
        button.classList.toggle("cursor-not-allowed", isSubmitting);
    }

    function updateFileLabel(input) {
        const key = input.getAttribute("data-file-label-target");
        const label = key ? document.querySelector(`[data-file-label="${key}"]`) : null;
        if (!label) {
            return;
        }

        label.textContent = input.files && input.files.length > 0
            ? input.files[0].name
            : DEFAULT_FILE_LABEL;
    }

    function resetForm(form, parts) {
        form.reset();
        if (parts.fileInput) {
            updateFileLabel(parts.fileInput);
        }
        if (parts.details) {
            parts.details.removeAttribute("open");
        }
    }

    function scrollThread(thread) {
        if (!thread) {
            return;
        }

        thread.scrollTop = thread.scrollHeight;
    }

    function renderAttachment(message) {
        const attachment = message && message.attachment ? message.attachment : null;
        if (!attachment || !attachment.href) {
            return "";
        }

        const href = escapeHtml(attachment.href);
        const originalName = escapeHtml(attachment.original_name || "Abrir anexo");
        const kind = String(attachment.kind || "document");

        if (kind === "image") {
            return [
                '<div class="mt-3">',
                `  <a class="block overflow-hidden rounded-2xl border border-white/10" href="${href}" target="_blank">`,
                `      <img alt="${originalName}" class="max-h-72 w-full object-cover" src="${href}">`,
                "  </a>",
                "</div>",
            ].join("");
        }

        const icon = kind === "video" ? "play_circle" : "description";
        return [
            '<div class="mt-3">',
            `  <a class="flex items-center gap-3 rounded-2xl bg-white/90 px-4 py-3 text-sm font-bold text-slate-800" href="${href}" target="_blank">`,
            `      <span class="material-symbols-outlined">${icon}</span>`,
            `      <span class="truncate">${originalName}</span>`,
            "  </a>",
            "</div>",
        ].join("");
    }

    function renderUnlockStatus(message) {
        if (!message || Number(message.unlock_price || 0) <= 0 || !message.creator_unlock_status) {
            return "";
        }

        const isUnlocked = message.creator_unlock_status === "unlocked";
        const labelClasses = isUnlocked ? "bg-emerald-100 text-emerald-700" : "bg-amber-100 text-amber-700";
        const unlockUser = message.creator_unlock_user_name
            ? `<span class="text-xs font-semibold text-white/80">${escapeHtml(message.creator_unlock_user_name)} &middot; ${escapeHtml(message.creator_unlock_at || "")}</span>`
            : "";

        return [
            '<div class="mt-3 flex flex-wrap items-center gap-2">',
            `  <span class="inline-flex rounded-full px-3 py-2 text-xs font-bold ${labelClasses}">${escapeHtml(message.creator_unlock_label || "Aguardando desbloqueio")}</span>`,
            '  <span class="inline-flex rounded-full bg-primary/10 px-3 py-2 text-xs font-bold text-primary">',
            '      <span class="inline-flex items-center gap-1.5 whitespace-nowrap">',
            `          <span>${escapeHtml(message.unlock_price || 0)}</span>`,
            `          <img alt="LuaCoin" class="h-3.5 w-3.5 shrink-0" src="${LUACOIN_ICON}">`,
            "      </span>",
            "  </span>",
            `  ${unlockUser}`,
            "</div>",
        ].join("");
    }

    function renderMessageBubble(message) {
        const body = String(message.body || "").trim();
        const bodyHtml = body !== "" ? `<p class="text-sm leading-6">${nl2br(body)}</p>` : "";
        const attachmentHtml = renderAttachment(message);
        const creatorStatusHtml = renderUnlockStatus(message);
        const timestamp = escapeHtml(message.created_at_label || "");
        const messageId = escapeHtml(message.id || "");

        return [
            `<article class="flex justify-end" data-chat-message-id="${messageId}">`,
            '  <div class="max-w-[92vw] rounded-[28px] bg-primary px-4 py-3 text-white shadow-sm sm:max-w-[82%]">',
            `      ${bodyHtml}`,
            `      ${attachmentHtml}`,
            `      ${creatorStatusHtml}`,
            `      <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.22em] text-white/70">${timestamp}</p>`,
            "  </div>",
            "</article>",
        ].join("");
    }

    function renderPendingMessage(body, fileName) {
        const safeBody = body ? `<p class="text-sm leading-6">${nl2br(body)}</p>` : "";
        const safeFileName = fileName
            ? `<p class="mt-3 rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold text-white/85">${escapeHtml(fileName)}</p>`
            : "";

        return [
            '<article class="flex justify-end" data-chat-pending-message="1">',
            '  <div class="max-w-[92vw] rounded-[28px] bg-primary/90 px-4 py-3 text-white shadow-sm sm:max-w-[82%]">',
            `      ${safeBody}`,
            `      ${safeFileName}`,
            '      <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.22em] text-white/70">Enviando...</p>',
            "  </div>",
            "</article>",
        ].join("");
    }

    function updateConversationPreview(form, previewText) {
        const conversationId = form.querySelector("input[name='conversation_id']")?.value || "";
        if (!conversationId || !previewText) {
            return;
        }

        const preview = document.querySelector(`[data-chat-conversation-card="${conversationId}"] [data-chat-preview]`);
        if (!preview) {
            return;
        }

        preview.textContent = previewText;

        const card = preview.closest("[data-chat-conversation-card]");
        const list = card?.parentElement;
        if (card && list && list.firstElementChild !== card) {
            list.prepend(card);
        }
    }

    async function submitComposeForm(form) {
        const parts = findComposeParts(form);
        const body = parts.textarea ? parts.textarea.value.trim() : "";
        const hasFile = !!(parts.fileInput && parts.fileInput.files && parts.fileInput.files.length > 0);

        setFeedback(parts.feedback, "", "info");

        if (!body && !hasFile) {
            setFeedback(parts.feedback, "Escreva uma mensagem ou escolha um anexo antes de enviar.", "error");
            parts.textarea?.focus();
            return;
        }

        let pendingNode = null;
        if (parts.threadItems) {
            const wrapper = document.createElement("div");
            wrapper.innerHTML = renderPendingMessage(body, hasFile ? parts.fileInput.files[0].name : "");
            pendingNode = wrapper.firstElementChild;
            if (pendingNode) {
                const emptyState = parts.threadItems.querySelector("[data-chat-empty-state]");
                if (emptyState) {
                    emptyState.remove();
                }
                parts.threadItems.appendChild(pendingNode);
                scrollThread(parts.thread);
            }
        }

        setSubmitting(parts.submitButton, true);

        try {
            const response = await fetch(form.action, {
                method: "POST",
                body: new FormData(form),
                credentials: "same-origin",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json",
                },
            });

            let payload = null;
            try {
                payload = await response.json();
            } catch (error) {
                payload = null;
            }

            if (!response.ok || !payload || !payload.ok || !payload.chat_message) {
                throw new Error(payload && payload.message ? payload.message : "Nao foi possivel enviar a mensagem agora.");
            }

            const messageHtml = renderMessageBubble(payload.chat_message);
            if (pendingNode) {
                pendingNode.outerHTML = messageHtml;
            } else if (parts.threadItems) {
                parts.threadItems.insertAdjacentHTML("beforeend", messageHtml);
            }

            updateConversationPreview(form, payload.preview_text || payload.chat_message.body || "Mensagem enviada.");
            resetForm(form, parts);
            setFeedback(parts.feedback, "", "info");
            scrollThread(parts.thread);
        } catch (error) {
            if (pendingNode) {
                pendingNode.remove();
            }
            setFeedback(parts.feedback, error instanceof Error ? error.message : "Nao foi possivel enviar a mensagem agora.", "error");
        } finally {
            setSubmitting(parts.submitButton, false);
        }
    }

    function initComposeForm(form) {
        if (form.dataset.chatComposerReady === "1") {
            return;
        }

        form.dataset.chatComposerReady = "1";
        form.addEventListener("submit", function (event) {
            event.preventDefault();
            void submitComposeForm(form);
        });
    }

    window.SexyLuaChatComposer = function () {
        document.querySelectorAll("[data-chat-compose]").forEach(initComposeForm);
    };
})();
