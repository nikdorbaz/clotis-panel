var dataTableCurrentId = 0;

var newTableId = function () {
    return dataTableCurrentId++;
};

var parseTabular = function (text) {
    text = text.replace(/\r/g, "");
    return text
        .split("\n")
        .filter(Boolean)
        .map((line) => line.split("\t"));
};

var encodeCellId = (id, i, j) => `cell${id}-${i}-${j}`;

var decodeCellId = function (name) {
    var match = /^cell(\d+)-(\d+)-(\d+)/.exec(name);
    return match
        ? [Number(match[1]), Number(match[2]), Number(match[3])]
        : null;
};

var getCellIndexes = (td) => decodeCellId(td.id).slice(1);

var inputElementOnMouseDown = (evt) => (evt.target.className = "");

var spreadsheet = {
    createTable: function ({
        rows = 10,
        columns = 10,
        textInputElement,
        headers = [],
        filterTypes = [],
        data = [],
        fixedRows = 0,
        fixedCols = 0,
    }) {
        var tableElement = document.createElement("table");
        tableElement.className = "spreadsheet";

        var thead = document.createElement("thead");
        var tbody = document.createElement("tbody");

        var tableId = newTableId();
        var filterRow = document.createElement("tr");
        filterRow.className = "filter-row fixed";

        var onFilterInput = function (e) {
            var colIndex = parseInt(e.target.dataset.colIndex);
            var filterValue = e.target.value.toLowerCase();

            for (var i = 0; i < tbody.rows.length; i++) {
                var row = tbody.rows[i];
                if (row.classList.contains("fixed")) continue;
                var cell = row.cells[colIndex];
                var text = cell.textContent.toLowerCase();
                row.style.display =
                    !filterValue || text.includes(filterValue) ? "" : "none";
            }
        };

        for (let j = 0; j < columns; j++) {
            let filterCell = document.createElement("th");
            let type = filterTypes[j];
            if (type === "text") {
                let input = document.createElement("input");
                input.type = "text";
                input.placeholder = "Поиск...";
                input.dataset.colIndex = j;
                input.oninput = onFilterInput;
                filterCell.appendChild(input);
            } else if (type === "select") {
                let select = document.createElement("select");
                select.dataset.colIndex = j;
                select.oninput = onFilterInput;
                let defaultOption = new Option("— Все —", "");
                select.appendChild(defaultOption);
                let uniqueValues = new Set();
                for (let i = 0; i < data.length; i++) {
                    if (data[i][j]) uniqueValues.add(data[i][j]);
                }
                [...uniqueValues].sort().forEach((val) => {
                    select.appendChild(new Option(val, val));
                });
                filterCell.appendChild(select);
            }
            filterRow.appendChild(filterCell);
        }

        thead.appendChild(filterRow);
        tableElement.appendChild(thead);
        tableElement.appendChild(tbody);

        for (let i = 0; i < rows; i++) {
            let tr = document.createElement("tr");
            if (i < fixedRows) {
                tr.classList.add("fixed");
                tr.setAttribute("cellpadding", 0);
                tr.style.padding = 0;
            }

            for (let j = 0; j < columns; j++) {
                let td = document.createElement("td");
                td.id = encodeCellId(tableId, i, j);
                td.setAttribute("contenteditable", true);
                td.setAttribute("cellpadding", 0);

                td.addEventListener("focus", (e) => {
                    const el = e.target;
                    const range = document.createRange();
                    const sel = window.getSelection();
                    range.selectNodeContents(el);
                    range.collapse(false);
                    sel.removeAllRanges();
                    sel.addRange(range);
                });

                td.addEventListener("input", () => {
                    td.classList.add("changed");
                });

                if (j < fixedCols) td.classList.add("fixed-col");
                td.textContent = (data[i] && data[i][j]) || "";
                tr.appendChild(td);
            }
            tbody.appendChild(tr);
        }

        textInputElement.className = "";
        textInputElement.onmousedown = inputElementOnMouseDown;

        requestAnimationFrame(() => {
            const fixedRowsEls = tableElement.querySelectorAll("tr.fixed");
            let offset = 0;

            fixedRowsEls.forEach((row, index) => {
                row.style.position = "sticky";
                row.style.top = offset + "px";
                row.style.zIndex = 100 - index;
                offset += row.offsetHeight;
            });
        });

        requestAnimationFrame(() => {
            const allRows = tableElement.querySelectorAll("tr");
            let colOffsets = [];

            for (let col = 0; col < fixedCols; col++) {
                const cell = allRows[0]?.cells[col];
                colOffsets[col] = cell ? cell.offsetLeft : 0;
            }

            allRows.forEach((row) => {
                for (let col = 0; col < fixedCols; col++) {
                    const cell = row.cells[col];
                    if (!cell) continue;
                    cell.style.position = "sticky";
                    cell.style.left = colOffsets[col] + "px";
                    cell.style.zIndex = 50;
                }
            });
        });

        return {
            element: tableElement,
            getText: function () {
                let lines = [];
                for (let i = 0; i < tbody.rows.length; i++) {
                    let row = tbody.rows[i];
                    if (row.classList.contains("filter-row")) continue;
                    let cells = Array.from(row.cells).map((td) =>
                        td.textContent.trim()
                    );
                    if (cells.some((c) => c !== ""))
                        lines.push(cells.join("\t"));
                }
                return lines.join("\n");
            },
        };
    },
};
