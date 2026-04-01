(function () {
    const feedMenus = Array.from(document.querySelectorAll("[data-feed-menu]"));

    if (feedMenus.length === 0) {
        return;
    }

    const toNumber = (value) => {
        const parsed = Number.parseInt(String(value || "0"), 10);
        return Number.isFinite(parsed) ? parsed : 0;
    };

    const updateMenuBadge = (menu) => {
        const storageKey = menu.getAttribute("data-feed-storage-key") || "";
        const badge = menu.querySelector("[data-feed-badge]");
        const items = Array.from(menu.querySelectorAll("[data-feed-item-marker]"));
        const seenMarker = toNumber(window.localStorage.getItem(storageKey));
        const unseenCount = items.filter((item) => toNumber(item.getAttribute("data-feed-item-marker")) > seenMarker).length;

        if (!badge) {
            return;
        }

        if (unseenCount > 0) {
            badge.textContent = String(Math.min(unseenCount, 99));
            badge.classList.remove("hidden");
        } else {
            badge.textContent = "";
            badge.classList.add("hidden");
        }
    };

    const markMenuSeen = (menu) => {
        const storageKey = menu.getAttribute("data-feed-storage-key") || "";
        const latestMarker = toNumber(menu.getAttribute("data-feed-latest-marker"));

        if (storageKey !== "" && latestMarker > 0) {
            window.localStorage.setItem(storageKey, String(latestMarker));
        }

        updateMenuBadge(menu);
    };

    feedMenus.forEach((menu) => {
        updateMenuBadge(menu);

        menu.addEventListener("toggle", () => {
            if (!menu.open) {
                return;
            }

            feedMenus.forEach((other) => {
                if (other !== menu) {
                    other.open = false;
                }
            });

            markMenuSeen(menu);
        });

        menu.querySelectorAll("a[href]").forEach((link) => {
            link.addEventListener("click", () => {
                markMenuSeen(menu);
            });
        });
    });

    document.addEventListener("click", (event) => {
        feedMenus.forEach((menu) => {
            if (menu.contains(event.target)) {
                return;
            }

            menu.open = false;
        });
    });
})();
