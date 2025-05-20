function applyStickyTable(tableId, fixedRow = 1, fixedCol = 1) {
    const table = document.getElementById(tableId);
    if (!table) return;

    // === 1. Обрабатываем строки ===
    let topOffset = 0;
    const rows = table.querySelectorAll("tr");

    for (let r = 0; r < fixedRow; r++) {
        const row = rows[r];
        if (!row) continue;

        row.style.position = "sticky";
        row.style.top = topOffset + "px";
        row.style.zIndex = 6;
        topOffset += row.offsetHeight;
    }

    // === 2. Обрабатываем колонки ===
    const colLeft = [];

    for (let c = 0; c < fixedCol; c++) {
        let maxWidth = 0;
        table.querySelectorAll("tr").forEach((row) => {
            let colIndex = 0;
            for (let i = 0; i < row.children.length; i++) {
                const cell = row.children[i];
                const colspan = parseInt(cell.getAttribute("colspan")) || 1;

                if (colspan > 1) {
                    colIndex += colspan;
                    continue;
                }

                if (colIndex === c) {
                    maxWidth = Math.max(maxWidth, cell.offsetWidth);
                    break;
                }

                colIndex += 1;
            }
        });

        const left = c === 0 ? 0 : colLeft[c - 1] + colLeft[c - 1 + "_w"];
        colLeft[c] = left;
        colLeft[c + "_w"] = maxWidth;

        table.querySelectorAll("tr").forEach((row) => {
            let colIndex = 0;
            for (let i = 0; i < row.children.length; i++) {
                const cell = row.children[i];
                const colspan = parseInt(cell.getAttribute("colspan")) || 1;

                if (!cell.classList.contains("fixed-col")) {
                    continue;
                }

                if (colspan > 1) {
                    colIndex += colspan;
                    cell.style.position = "sticky";
                    cell.style.left = 0 + "px";
                    cell.style.zIndex = 5;
                    continue;
                }

                if (colIndex === c) {
                    cell.style.position = "sticky";
                    cell.style.left = left + "px";
                    cell.style.zIndex = 5;
                    break;
                }

                colIndex += 1;
            }
        });
    }
}
