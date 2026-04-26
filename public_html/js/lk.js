// ========== ОСНОВНЫЕ ПЕРЕМЕННЫЕ ==========
const SVG_NS = "http://www.w3.org/2000/svg";

// Стадии роста дерева
const STAGES = [
    "Семечко посажено 🌰",
    "Первый росточек 🌱",
    "Росточек тянется к солнцу ☀️",
    "Появились веточки 🌿",
    "Деревце крепнет 💪",
    "Крона начинает расти 🌳",
    "Пышная крона 🌲",
    "Появились цветы 🌸",
    "Зреют яблочки 🍏",
    "Яблоня выросла! 🍎"
];

const CROWN_COLORS = [
    "#a8d5a2", "#8fca87", "#76bf6c", "#5db451",
    "#4aaa3e", "#3a9f30", "#2e9426", "#228a1c",
    "#167f12", "#0a750a"
];

// Текущий уровень и хранилище
let treeLevel = parseInt(localStorage.getItem("treeLevel")) || 1;
let spinnerAnimation = null;

// ========== ЗАДАНИЯ ДЛЯ КОЛЕСА (только одно объявление!) ==========
function cleanShortText(text) {
    if (!text) return text;
    return text.replace(/\?/g, '');
}
const WHEEL_SECTORS = (typeof WHEEL_SECTORS_FROM_PHP !== 'undefined' && WHEEL_SECTORS_FROM_PHP.length > 0)
    ? WHEEL_SECTORS_FROM_PHP.slice(0, 9)
    : [
        { short: "Задание 1", title: "Сортировка мусора", desc: "Рассортируй домашний мусор: пластик, бумагу, стекло и органику. Сфотографируй результат!" },
        { short: "Задание 2", title: "Экономия воды", desc: "Принимай душ на 2 минуты меньше обычного. Расскажи семье о важности экономии воды!" },
        { short: "Задание 3", title: "Многоразовая бутылка", desc: "Используй многоразовую бутылку для воды вместо пластиковой. Поделись фото!" },
        { short: "Задание 4", title: "Сбор батареек", desc: "Найди ближайший пункт приёма батареек и сдай использованные элементы питания!" },
        { short: "Задание 5", title: "Посади растение", desc: "Посади цветок или дерево. Ухаживай за ним и наблюдай за ростом!" },
        { short: "Задание 6", title: "Экосумка", desc: "Сходи в магазин с эко-сумкой вместо пластиковых пакетов. Запиши свои впечатления!" },
        { short: "Задание 7", title: "Экономия энергии", desc: "Выключай свет и электроприборы, когда они не нужны. Посчитай, сколько сэкономил!" },
        { short: "Задание 8", title: "Уборка территории", desc: "Прими участие в субботнике или убери мусор в ближайшем парке!" },
        { short: "Задание 9", title: "Поделись знаниями", desc: "Расскажи друзьям или в соцсетях об экопривычках. Вдохнови других!" }
    ];

console.log("Колесо использует заданий:", WHEEL_SECTORS.length);

// ========== РИСОВАНИЕ ДЕРЕВА ==========
function drawTree(svg, level) {
    if (!svg) return;
    svg.innerHTML = "";
    const L = Math.min(Math.max(level, 1), 10);
    const cx = 150;

    const earth = document.createElementNS(SVG_NS, "ellipse");
    earth.setAttribute("cx", cx);
    earth.setAttribute("cy", 302);
    earth.setAttribute("rx", 65);
    earth.setAttribute("ry", 9);
    earth.setAttribute("fill", "#8aae7a");
    earth.setAttribute("opacity", "0.45");
    svg.appendChild(earth);

    if (L === 1) {
        const seed1 = document.createElementNS(SVG_NS, "ellipse");
        seed1.setAttribute("cx", cx);
        seed1.setAttribute("cy", 292);
        seed1.setAttribute("rx", 13);
        seed1.setAttribute("ry", 9);
        seed1.setAttribute("fill", "#8B5E3C");
        svg.appendChild(seed1);

        const seed2 = document.createElementNS(SVG_NS, "ellipse");
        seed2.setAttribute("cx", cx + 3);
        seed2.setAttribute("cy", 288);
        seed2.setAttribute("rx", 5);
        seed2.setAttribute("ry", 3);
        seed2.setAttribute("fill", "#a07040");
        seed2.setAttribute("opacity", "0.6");
        svg.appendChild(seed2);
        return;
    }

    if (L === 2) {
        const trunk = document.createElementNS(SVG_NS, "rect");
        trunk.setAttribute("x", 148);
        trunk.setAttribute("y", 258);
        trunk.setAttribute("width", 4);
        trunk.setAttribute("height", 34);
        trunk.setAttribute("rx", 2);
        trunk.setAttribute("fill", "#6B8C42");
        svg.appendChild(trunk);

        const crown1 = document.createElementNS(SVG_NS, "ellipse");
        crown1.setAttribute("cx", cx);
        crown1.setAttribute("cy", 253);
        crown1.setAttribute("rx", 11);
        crown1.setAttribute("ry", 8);
        crown1.setAttribute("fill", "#76bf6c");
        svg.appendChild(crown1);
        return;
    }

    const trunkH = 38 + L * 8;
    const trunkW = 10 + L * 2;
    const trunkX = cx - trunkW / 2;
    const trunkY = 298 - trunkH;

    const trunk = document.createElementNS(SVG_NS, "rect");
    trunk.setAttribute("x", trunkX);
    trunk.setAttribute("y", trunkY);
    trunk.setAttribute("width", trunkW);
    trunk.setAttribute("height", trunkH);
    trunk.setAttribute("rx", trunkW / 3);
    trunk.setAttribute("fill", "#7a4f2a");
    svg.appendChild(trunk);

    const cr = 18 + L * 8;
    const ccy = trunkY + 8;
    const cc = CROWN_COLORS[L - 1];

    const mainCrown = document.createElementNS(SVG_NS, "circle");
    mainCrown.setAttribute("cx", cx);
    mainCrown.setAttribute("cy", ccy);
    mainCrown.setAttribute("r", cr);
    mainCrown.setAttribute("fill", cc);
    mainCrown.setAttribute("opacity", "0.88");
    svg.appendChild(mainCrown);

    if (L >= 5) {
        const leftCrown = document.createElementNS(SVG_NS, "circle");
        leftCrown.setAttribute("cx", cx - cr * 0.55);
        leftCrown.setAttribute("cy", ccy + cr * 0.22);
        leftCrown.setAttribute("r", cr * 0.76);
        leftCrown.setAttribute("fill", cc);
        leftCrown.setAttribute("opacity", "0.82");
        svg.appendChild(leftCrown);

        const rightCrown = document.createElementNS(SVG_NS, "circle");
        rightCrown.setAttribute("cx", cx + cr * 0.55);
        rightCrown.setAttribute("cy", ccy + cr * 0.22);
        rightCrown.setAttribute("r", cr * 0.76);
        rightCrown.setAttribute("fill", cc);
        rightCrown.setAttribute("opacity", "0.82");
        svg.appendChild(rightCrown);
    }

    if (L >= 6) {
        const topCrown = document.createElementNS(SVG_NS, "circle");
        topCrown.setAttribute("cx", cx);
        topCrown.setAttribute("cy", ccy - cr * 0.45);
        topCrown.setAttribute("r", cr * 0.72);
        topCrown.setAttribute("fill", cc);
        topCrown.setAttribute("opacity", "0.92");
        svg.appendChild(topCrown);
    }

    if (L >= 7) {
        const leftTop = document.createElementNS(SVG_NS, "circle");
        leftTop.setAttribute("cx", cx - cr * 0.42);
        leftTop.setAttribute("cy", ccy - cr * 0.55);
        leftTop.setAttribute("r", cr * 0.56);
        leftTop.setAttribute("fill", cc);
        leftTop.setAttribute("opacity", "0.86");
        svg.appendChild(leftTop);

        const rightTop = document.createElementNS(SVG_NS, "circle");
        rightTop.setAttribute("cx", cx + cr * 0.42);
        rightTop.setAttribute("cy", ccy - cr * 0.55);
        rightTop.setAttribute("r", cr * 0.56);
        rightTop.setAttribute("fill", cc);
        rightTop.setAttribute("opacity", "0.86");
        svg.appendChild(rightTop);
    }

        // ========== ЦВЕТОЧКИ (уровень 8) ==========
    if (L === 8) {
        const flowerColors = ["#FFB7B2", "#FFDAC1", "#E2F0CB", "#B5EAD7", "#C7CEEA"];
        const flowerPositions = [
            { x: cx - 22, y: ccy - 15, size: 6 },
            { x: cx + 18, y: ccy - 18, size: 5 },
            { x: cx - 10, y: ccy - 35, size: 5 },
            { x: cx + 12, y: ccy - 40, size: 6 },
            { x: cx - 30, y: ccy + 5, size: 5 },
            { x: cx + 28, y: ccy + 2, size: 5 },
            { x: cx - 5, y: ccy - 50, size: 4 },
            { x: cx + 5, y: ccy - 52, size: 4 }
        ];
        
        for (let i = 0; i < flowerPositions.length; i++) {
            const pos = flowerPositions[i];
            const petalColor = flowerColors[i % flowerColors.length];
            
            for (let p = 0; p < 5; p++) {
                const angle = (p * 72) * Math.PI / 180;
                const petalX = pos.x + Math.cos(angle) * pos.size * 0.7;
                const petalY = pos.y + Math.sin(angle) * pos.size * 0.7;
                const petal = document.createElementNS(SVG_NS, "circle");
                petal.setAttribute("cx", petalX);
                petal.setAttribute("cy", petalY);
                petal.setAttribute("r", pos.size * 0.6);
                petal.setAttribute("fill", petalColor);
                svg.appendChild(petal);
            }
            
            const center = document.createElementNS(SVG_NS, "circle");
            center.setAttribute("cx", pos.x);
            center.setAttribute("cy", pos.y);
            center.setAttribute("r", pos.size * 0.5);
            center.setAttribute("fill", "#FFE066");
            svg.appendChild(center);
        }
    }

    // ========== ЯБЛОЧКИ (уровень 9 и 10) ==========
    if (L >= 9) {
        // Функция для генерации равномерных позиций яблок по кроне
        function getApplePositions(centerX, centerY, crownRadius, level) {
            const positions = [];
            // Количество яблок в зависимости от уровня
            let count = level === 9 ? 10 : 16;
            
            // Равномерно распределяем яблоки по окружности с разными радиусами
            for (let i = 0; i < count; i++) {
                // Угол равномерно распределён
                let angle = (i / count) * Math.PI * 2;
                // Случайный сдвиг для естественности
                angle += (Math.sin(i * 137.5) * 0.1);
                
                // Радиус от центра (чтобы яблоки были по всей кроне)
                const radiusVariation = 0.45 + Math.abs(Math.sin(angle * 2)) * 0.35;
                const radius = crownRadius * radiusVariation;
                
                let x = centerX + Math.cos(angle) * radius;
                let y = centerY + Math.sin(angle) * radius * 0.7;
                
                // Размер яблока
                let size = level === 9 ? 6 : 7;
                
                positions.push({ x, y, size, angle });
            }
            
            // Для 10 уровня добавляем несколько яблок ближе к центру
            if (level === 10) {
                for (let i = 0; i < 4; i++) {
                    const angle = (i / 4) * Math.PI * 2 + 0.5;
                    const radius = crownRadius * 0.25;
                    let x = centerX + Math.cos(angle) * radius;
                    let y = centerY + Math.sin(angle) * radius * 0.6;
                    positions.push({ x, y, size: 6, angle });
                }
            }
            
            return positions;
        }
        
        const applePositions = getApplePositions(cx, ccy, cr, L);
        
        for (let i = 0; i < applePositions.length; i++) {
            const pos = applePositions[i];
            
            const apple = document.createElementNS(SVG_NS, "circle");
            apple.setAttribute("cx", pos.x);
            apple.setAttribute("cy", pos.y);
            apple.setAttribute("r", pos.size);
            apple.setAttribute("fill", "#e74c3c");
            apple.setAttribute("stroke", "#c0392b");
            apple.setAttribute("stroke-width", "1.5");
            svg.appendChild(apple);
            
            const highlight = document.createElementNS(SVG_NS, "circle");
            highlight.setAttribute("cx", pos.x - 2);
            highlight.setAttribute("cy", pos.y - 2);
            highlight.setAttribute("r", pos.size * 0.25);
            highlight.setAttribute("fill", "#ff8a7a");
            highlight.setAttribute("opacity", "0.6");
            svg.appendChild(highlight);
            
            const leaf = document.createElementNS(SVG_NS, "ellipse");
            leaf.setAttribute("cx", pos.x - 3);
            leaf.setAttribute("cy", pos.y - pos.size - 1);
            leaf.setAttribute("rx", 4);
            leaf.setAttribute("ry", 2.5);
            leaf.setAttribute("fill", "#27ae60");
            leaf.setAttribute("transform", `rotate(-30, ${pos.x - 3}, ${pos.y - pos.size - 1})`);
            svg.appendChild(leaf);
            
            const stem = document.createElementNS(SVG_NS, "line");
            stem.setAttribute("x1", pos.x - 1);
            stem.setAttribute("y1", pos.y - pos.size - 1);
            stem.setAttribute("x2", pos.x - 3);
            stem.setAttribute("y2", pos.y - pos.size - 4);
            stem.setAttribute("stroke", "#8B5E3C");
            stem.setAttribute("stroke-width", "1.5");
            svg.appendChild(stem);
        }
    }

    // Крутящееся солнце
    if (L < 10) {
        const spinnerGroup = document.createElementNS(SVG_NS, "g");
        spinnerGroup.setAttribute("class", "spinner");
        spinnerGroup.setAttribute("transform", `translate(${cx + 80}, ${ccy - 100})`);

        for (let i = 0; i < 8; i++) {
            const ray = document.createElementNS(SVG_NS, "line");
            const angle = (i * 45) * Math.PI / 180;
            const x1 = 12 * Math.cos(angle);
            const y1 = 12 * Math.sin(angle);
            const x2 = 22 * Math.cos(angle);
            const y2 = 22 * Math.sin(angle);
            ray.setAttribute("x1", x1);
            ray.setAttribute("y1", y1);
            ray.setAttribute("x2", x2);
            ray.setAttribute("y2", y2);
            ray.setAttribute("stroke", "#ffd966");
            ray.setAttribute("stroke-width", "2.5");
            ray.setAttribute("stroke-linecap", "round");
            spinnerGroup.appendChild(ray);
        }

        const centerCircle = document.createElementNS(SVG_NS, "circle");
        centerCircle.setAttribute("cx", 0);
        centerCircle.setAttribute("cy", 0);
        centerCircle.setAttribute("r", 8);
        centerCircle.setAttribute("fill", "#ffb347");
        spinnerGroup.appendChild(centerCircle);

        svg.appendChild(spinnerGroup);

        let angle = 0;
        if (spinnerAnimation) clearInterval(spinnerAnimation);
        spinnerAnimation = setInterval(() => {
            const sp = svg.querySelector('.spinner');
            if (sp) {
                angle = (angle + 8) % 360;
                sp.setAttribute("transform", `translate(${cx + 80}, ${ccy - 100}) rotate(${angle})`);
            }
        }, 80);
    } else if (spinnerAnimation) {
        clearInterval(spinnerAnimation);
        spinnerAnimation = null;
    }
}

function updateTreeUI() {
    const L = Math.min(Math.max(treeLevel, 1), 10);

    const levelLabel = document.getElementById("levelLabel");
    const stageText = document.getElementById("stageText");
    const progressFill = document.getElementById("progressFill");
    const dots = document.getElementById("levelDots");

    if (levelLabel) levelLabel.textContent = `Уровень ${L} из 10`;
    if (stageText) stageText.textContent = STAGES[L - 1];
    if (progressFill) progressFill.style.width = `${(L / 10) * 100}%`;

    if (dots) {
        dots.innerHTML = "";
        for (let i = 1; i <= 10; i++) {
            const d = document.createElement("div");
            d.className = "dot" + (i < L ? " done" : i === L ? " active" : "");
            dots.appendChild(d);
        }
    }

    const svg = document.getElementById("treeSvg");
    if (svg) drawTree(svg, L);
}

function showMessage(text) {
    const msg = document.getElementById("treeMessage");
    if (msg) {
        msg.textContent = text;
        msg.style.display = "block";
        msg.style.opacity = "1";
        setTimeout(() => {
            msg.style.opacity = "0";
            setTimeout(() => {
                msg.style.display = "none";
                msg.style.opacity = "1";
            }, 500);
        }, 3000);
    } else {
        alert(text);
    }
}

// ========== РОСТ ДЕРЕВА ==========
function completeTask() {
    if (treeLevel < 10) {
        treeLevel = Math.min(treeLevel + 1, 10);
        localStorage.setItem("treeLevel", treeLevel);

        const wrap = document.querySelector(".tree-svg-container");
        if (wrap) {
            wrap.classList.add("pop");
            setTimeout(() => wrap.classList.remove("pop"), 500);
        }

        updateTreeUI();
        showMessage(treeLevel === 10 ? "Поздравляем! Яблоня полностью выросла! 🍎" : "Отлично! Твоё дерево выросло! 🌳");
    } else {
        showMessage("Твоё дерево уже достигло максимального уровня! 🍎");
    }
}

// ========== УМЕНЬШЕНИЕ ДЕРЕВА ==========
function skipTask() {
    if (treeLevel > 1) {
        treeLevel = Math.max(treeLevel - 1, 1);
        localStorage.setItem("treeLevel", treeLevel);

        const wrap = document.querySelector(".tree-svg-container");
        if (wrap) {
            wrap.classList.add("pop");
            setTimeout(() => wrap.classList.remove("pop"), 500);
        }

        updateTreeUI();
        showMessage("🌧️ Дерево немного завяло... Поливай его чаще! 🌧️");
    } else {
        showMessage("Дерево уже на начальном уровне! 🌰");
    }
}

// ========== ИНИЦИАЛИЗАЦИЯ ДЕРЕВА ==========
function initTreeWidget() {
    const container = document.getElementById("treeWidget");
    if (!container) {
        console.error("treeWidget не найден!");
        return;
    }

    container.innerHTML = `
        <div class="tree-title">Твоё дерево 🌱</div>
        <div class="tree-subtitle" id="levelLabel">Уровень ${treeLevel} из 10</div>
        
        <div class="tree-svg-container" style="display: flex; justify-content: center; margin: 20px 0;">
            <svg id="treeSvg" width="300" height="310" viewBox="0 0 300 310" style="cursor: pointer;"></svg>
        </div>
        <div class="stage-text" id="stageText">${STAGES[treeLevel - 1]}</div>
        <div class="progress-bar-bg">
            <div class="progress-bar-fill" id="progressFill" style="width: ${(treeLevel / 10) * 100}%;"></div>
        </div>
        <div class="level-dots" id="levelDots"></div>
        
        <div style="display: flex; gap: 15px; justify-content: center; margin-top: 15px;">
            <button class="tree-btn" onclick="completeTask()" style="background: #8aae7a;">💧 Полить дерево</button>
            <button class="tree-btn" onclick="skipTask()" style="background: #e0a878;">🍂 Пропустить задание</button>
        </div>
        
        <div class="tree-message" id="treeMessage"></div>
    `;

    updateTreeUI();
}

// ========== КАЛЕНДАРЬ С ПОДСВЕТКОЙ МЕРОПРИЯТИЙ (без AJAX) ==========
let currentDate = new Date();
let eventsData = [];

const monthsRu = [
    "Январь", "Февраль", "Март", "Апрель", "Май", "Июнь",
    "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"
];

// Получение мероприятий за конкретный день
function getEventsForDate(year, month, day) {
    const formattedDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    return eventsData.filter(event => event.date === formattedDate);
}

// Показ модального окна с информацией о мероприятии
function showEventInfo(events, day) {
    const oldModal = document.querySelector('.event-modal');
    if (oldModal) oldModal.remove();
    
    const modal = document.createElement('div');
    modal.className = 'event-modal';
    
    let eventsHTML = '';
    events.forEach(event => {
        const eventUrl = `events.php#event${event.id}`;
        eventsHTML += `
            <div class="event-item">
                <h4>${escapeHtml(event.title)}</h4>
                <p>📍 ${escapeHtml(event.location)}<br>⏰ ${escapeHtml(event.time)}</p>
                <a href="${eventUrl}" class="event-link">📖 Узнать о событии</a>
            </div>
        `;
    });
    
    modal.innerHTML = `
        <div class="event-modal-content">
            <div class="event-modal-header">
                <h3>📅 События на ${day} число</h3>
                <button class="event-modal-close">&times;</button>
            </div>
            ${eventsHTML}
        </div>
    `;
    
    document.body.appendChild(modal);
    
    modal.querySelector('.event-modal-close').addEventListener('click', () => {
        modal.remove();
    });
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    const monthYearEl = document.getElementById("monthYearDisplay");
    if (monthYearEl) {
        monthYearEl.textContent = `${monthsRu[month]} ${year}`;
    }

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();

    const calendarDays = document.getElementById("calendarDays");
    if (!calendarDays) return;

    calendarDays.innerHTML = "";

    let startDay = firstDay === 0 ? 6 : firstDay - 1;

    // Дни предыдущего месяца
    for (let i = startDay - 1; i >= 0; i--) {
        const dayEl = document.createElement("div");
        dayEl.textContent = daysInPrevMonth - i;
        dayEl.classList.add("other-month");
        calendarDays.appendChild(dayEl);
    }

    const today = new Date();
    const isCurrentMonth = (today.getMonth() === month && today.getFullYear() === year);

    // Дни текущего месяца
    for (let day = 1; day <= daysInMonth; day++) {
        const dayEl = document.createElement("div");
        dayEl.textContent = day;
        
        const eventsOnDay = getEventsForDate(year, month, day);
        const hasEvents = eventsOnDay.length > 0;
        
        if (isCurrentMonth && day === today.getDate()) {
            dayEl.classList.add("today");
        }
        
        if (hasEvents) {
            dayEl.classList.add("has-event");
            dayEl.style.cursor = "pointer";
            dayEl.addEventListener('click', () => {
                showEventInfo(eventsOnDay, day);
            });
        }
        
        calendarDays.appendChild(dayEl);
    }

    // Дни следующего месяца
    const totalCells = 42;
    const filledCells = startDay + daysInMonth;
    const remaining = totalCells - filledCells;

    for (let day = 1; day <= remaining; day++) {
        const dayEl = document.createElement("div");
        dayEl.textContent = day;
        dayEl.classList.add("other-month");
        calendarDays.appendChild(dayEl);
    }
}

function initCalendar() {
    const prevBtn = document.getElementById("prevMonth");
    const nextBtn = document.getElementById("nextMonth");

    if (prevBtn) {
        prevBtn.addEventListener("click", () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener("click", () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });
    }

    // Загружаем данные из глобальной переменной
    if (typeof calendarEventsData !== 'undefined') {
        eventsData = calendarEventsData;
    }
    
    renderCalendar();
}

function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Запускаем календарь, когда страница загрузилась
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('calendarDays')) {
        initCalendar();
    }
});

// ========== КОЛЕСО ФОРТУНЫ ==========
const SECTOR_COLORS = [
    "#a8d5a2", "#8fca87", "#76bf6c", "#5db451",
    "#4aaa3e", "#3a9f30", "#2e9426", "#228a1c", "#167f12"
];

let currentRotation = 0;
let lastSpinTime = localStorage.getItem("lastSpinTime") || null;
let cooldownInterval = null;

function canSpinWheel() {
    if (!lastSpinTime) return true;
    const timeSinceLastSpin = Date.now() - parseInt(lastSpinTime);
    return timeSinceLastSpin >= 10000;
}

function getTimeUntilNextSpin() {
    if (!lastSpinTime) return 0;
    const elapsed = Date.now() - parseInt(lastSpinTime);
    return Math.max(0, 10000 - elapsed);
}

function updateSpinButtonText(btn) {
    if (!btn) return;
    if (canSpinWheel()) {
        btn.textContent = "Крутить колесо";
        btn.disabled = false;
        btn.style.opacity = "1";
    } else {
        const remaining = getTimeUntilNextSpin();
        const seconds = Math.ceil(remaining / 1000);
        btn.textContent = `⏳ Подождите ${seconds} сек...`;
        btn.disabled = true;
        btn.style.opacity = "0.6";
    }
}

function startCooldownTimer(btn) {
    if (cooldownInterval) clearInterval(cooldownInterval);
    
    const updateTimer = () => {
        updateSpinButtonText(btn);
        if (canSpinWheel()) {
            if (cooldownInterval) clearInterval(cooldownInterval);
            cooldownInterval = null;
        }
    };
    
    updateTimer();
    cooldownInterval = setInterval(updateTimer, 500);
}

function drawWheel(svg) {
    if (!svg) return;
    svg.innerHTML = "";

    const size = 420;
    const cx = size / 2;
    const cy = size / 2;
    const radius = 170;
    const numSectors = WHEEL_SECTORS.length;
    const sectorAngle = 360 / numSectors;

    const wheelGroup = document.createElementNS(SVG_NS, "g");
    wheelGroup.setAttribute("id", "wheelGroup");
    wheelGroup.style.transformOrigin = `${cx}px ${cy}px`;
    wheelGroup.style.transform = `rotate(${currentRotation}deg)`;

    svg.appendChild(wheelGroup);

    for (let i = 0; i < numSectors; i++) {
        const startAngle = -90 + i * sectorAngle;
        const endAngle = startAngle + sectorAngle;
        const startRad = (startAngle * Math.PI) / 180;
        const endRad = (endAngle * Math.PI) / 180;

        const x1 = cx + radius * Math.cos(startRad);
        const y1 = cy + radius * Math.sin(startRad);
        const x2 = cx + radius * Math.cos(endRad);
        const y2 = cy + radius * Math.sin(endRad);

        const largeArc = (sectorAngle > 180) ? 1 : 0;
        const pathD = `M ${cx},${cy} L ${x1},${y1} A ${radius},${radius} 0 ${largeArc} 1 ${x2},${y2} Z`;

        const path = document.createElementNS(SVG_NS, "path");
        path.setAttribute("d", pathD);
        path.setAttribute("fill", SECTOR_COLORS[i % SECTOR_COLORS.length]);
        path.setAttribute("stroke", "#ffffff");
        path.setAttribute("stroke-width", "6");
        wheelGroup.appendChild(path);

        const midAngle = startAngle + sectorAngle / 2;
        const midRad = (midAngle * Math.PI) / 180;
        const textRadius = radius * 0.68;
        const tx = cx + textRadius * Math.cos(midRad);
        const ty = cy + textRadius * Math.sin(midRad);

        const text = document.createElementNS(SVG_NS, "text");
        text.setAttribute("x", tx);
        text.setAttribute("y", ty);
        text.setAttribute("fill", "#ffffff");
        text.setAttribute("font-size", "14");
        text.setAttribute("font-weight", "700");
        text.setAttribute("text-anchor", "middle");
        text.setAttribute("dominant-baseline", "middle");
        text.setAttribute("transform", `rotate(${midAngle} ${tx} ${ty})`);
        text.textContent = cleanShortText(WHEEL_SECTORS[i]?.short) || `Задание ${i+1}`;
        wheelGroup.appendChild(text);
    }

    const hub = document.createElementNS(SVG_NS, "circle");
    hub.setAttribute("cx", cx);
    hub.setAttribute("cy", cy);
    hub.setAttribute("r", "42");
    hub.setAttribute("fill", "#7a4f2a");
    wheelGroup.appendChild(hub);

    const hubInner = document.createElementNS(SVG_NS, "circle");
    hubInner.setAttribute("cx", cx);
    hubInner.setAttribute("cy", cy);
    hubInner.setAttribute("r", "30");
    hubInner.setAttribute("fill", "#f4e9d8");
    wheelGroup.appendChild(hubInner);

    const pointer = document.createElementNS(SVG_NS, "polygon");
    pointer.setAttribute("points", 
        `${cx-20},${cy - radius - 28} ` +
        `${cx+20},${cy - radius - 28} ` +
        `${cx},${cy - radius + 8}`
    );
    pointer.setAttribute("fill", "#ffd966");
    pointer.setAttribute("stroke", "#2e9426");
    pointer.setAttribute("stroke-width", "6");
    pointer.setAttribute("stroke-linejoin", "round");
    svg.appendChild(pointer);
}

function spinWheel() {
    const svg = document.getElementById("wheelSvg");
    if (!svg) return;
    
    if (!canSpinWheel()) {
        const remaining = getTimeUntilNextSpin();
        const seconds = Math.ceil(remaining / 1000);
        showMessage(`Подождите ${seconds} секунд перед следующим вращением! ⏳`);
        return;
    }

    if (!WHEEL_SECTORS || WHEEL_SECTORS.length === 0) {
        showMessage("Задания не загружены! Попробуйте позже.");
        return;
    }

    const selectedIndex = Math.floor(Math.random() * WHEEL_SECTORS.length);
    const spins = 8 + Math.random() * 5;
    const anglePerSector = 360 / WHEEL_SECTORS.length;

    const targetAngle = 360 * spins - 90 - (selectedIndex * anglePerSector + anglePerSector / 2);

    const wheelGroup = svg.querySelector("#wheelGroup");
    if (!wheelGroup) return;

    wheelGroup.style.transition = "none";
    wheelGroup.style.transform = `rotate(${currentRotation}deg)`;
    void wheelGroup.offsetWidth;

    wheelGroup.style.transition = "transform 4800ms cubic-bezier(0.25, 0.1, 0.25, 1)";
    wheelGroup.style.transform = `rotate(${targetAngle}deg)`;

    currentRotation = targetAngle;

    wheelGroup.addEventListener("transitionend", function handler() {
        wheelGroup.removeEventListener("transitionend", handler);

        currentRotation = targetAngle % 360;
        wheelGroup.style.transition = "none";
        wheelGroup.style.transform = `rotate(${currentRotation}deg)`;

        lastSpinTime = Date.now();
        localStorage.setItem("lastSpinTime", lastSpinTime);

        const spinBtn = document.getElementById("spinBtn");
        if (spinBtn) startCooldownTimer(spinBtn);

        const resultBlock = document.getElementById("wheelResult");
        if (resultBlock && WHEEL_SECTORS[selectedIndex]) {
            document.getElementById("resultTitle").textContent = WHEEL_SECTORS[selectedIndex].title;
            document.getElementById("resultDesc").textContent = WHEEL_SECTORS[selectedIndex].desc;
            resultBlock.style.display = "block";
            resultBlock.style.animation = "popIn 0.4s ease";
        }
    }, { once: true });
}

function hideWheelResult() {
    const block = document.getElementById("wheelResult");
    if (block) {
        block.style.display = "none";
    }
}

function initWheelWidget() {
    const container = document.getElementById("wheelWidget");
    if (!container) {
        console.error("wheelWidget не найден!");
        return;
    }

    container.innerHTML = `
        <div class="tree-title" style="font-size:28px;"> Колесо фортуны </div>
        <div class="tree-subtitle" style="margin-bottom:15px;">
            Крути колесо каждые 10 секунд и получай экологические задания!
        </div>
        
        <div class="tree-svg-container" style="display:flex; justify-content:center; margin:20px 0;">
            <svg id="wheelSvg" width="420" height="420" viewBox="0 0 420 420" style="cursor:pointer;"></svg>
        </div>

        <button id="spinBtn" class="tree-btn" style="font-size:22px; padding:18px 40px; width:100%; max-width:380px; margin:0 auto; display:block;">
            Крутить колесо
        </button>

        <div id="wheelResult" style="display:none; margin:25px auto; max-width:380px; padding:25px; 
                                     background:linear-gradient(135deg, #e8f5e9, #c8e6c9); 
                                     border-radius:20px; box-shadow:0 10px 30px rgba(46,148,38,0.15); 
                                     text-align:center; animation:popIn 0.4s ease;">
            <div style="font-size:28px; margin-bottom:8px;">🎉 Задание выпало!</div>
            <h3 id="resultTitle" style="margin:0 0 15px; color:#2e9426; font-size:22px;"></h3>
            <p id="resultDesc" style="margin:0; font-size:16px; line-height:1.6; color:#333;"></p>
            
            <button onclick="hideWheelResult()" 
                    style="margin-top:20px; background:#2e9426; color:white; border:none; 
                           padding:12px 30px; border-radius:50px; font-size:16px; cursor:pointer;">
                ✅ Закрыть
            </button>
        </div>
    `;

    const svg = document.getElementById("wheelSvg");
    if (svg) {
        currentRotation = 0;
        drawWheel(svg);
    }

    const spinBtn = document.getElementById("spinBtn");
    if (spinBtn) {
        spinBtn.addEventListener("click", spinWheel);
        startCooldownTimer(spinBtn);
    }
}

// ========== ДОПОЛНИТЕЛЬНЫЕ ФУНКЦИИ ==========
function changePassword() {
    const newPassword = prompt('Введите новый пароль:');
    
    if (!newPassword) return;
    if (newPassword.length < 6) {
        alert('Пароль должен содержать не менее 6 символов');
        return;
    }
    
    const hasUpperCase = /[A-ZА-ЯЁ]/.test(newPassword);
    const hasLowerCase = /[a-zа-яё]/.test(newPassword);
    const hasNumbers = /[0-9]/.test(newPassword);
    
    if (!hasUpperCase) {
        alert('Пароль должен содержать хотя бы одну заглавную букву');
        return;
    }
    if (!hasLowerCase) {
        alert('Пароль должен содержать хотя бы одну строчную букву');
        return;
    }
    if (!hasNumbers) {
        alert('Пароль должен содержать хотя бы одну цифру (0-9)');
        return;
    }
    
    alert('Пароль успешно изменен!');
    localStorage.setItem("userPassword", newPassword);
}

function initMobileMenu() {
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const navLeft = document.getElementById('navLeft');
    const navRight = document.getElementById('navRight');

    if (mobileBtn) {
        mobileBtn.addEventListener('click', () => {
            navLeft.classList.toggle('active');
            navRight.classList.toggle('active');
        });
    }
}

function isUserLoggedIn() {
    return localStorage.getItem('userLoggedIn') === 'true' || 
           document.cookie.indexOf('PHPSESSID') !== -1;
}

function initProfileMenu() {
    const profileIcon = document.getElementById('profileIcon');
    const dropdown = document.getElementById('profileDropdown');

    if (!profileIcon || !dropdown) return;

    profileIcon.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('show');
    });

    document.addEventListener('click', () => {
        dropdown.classList.remove('show');
    });

    const dropdownLinks = dropdown.querySelectorAll('a');
    dropdownLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            if (!isUserLoggedIn()) {
                e.preventDefault();
                window.location.href = 'vhod.php';
            }
        });
    });
}

// ========== ДОБАВЛЯЕМ СТИЛЬ АНИМАЦИИ ==========
const wheelStyle = document.createElement('style');
wheelStyle.textContent = `
    @keyframes popIn {
        0% { opacity: 0; transform: scale(0.9); }
        100% { opacity: 1; transform: scale(1); }
    }
    .pop {
        animation: pop 0.3s ease;
    }
    @keyframes pop {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
`;
document.head.appendChild(wheelStyle);

// ========== ГЛОБАЛЬНЫЕ ФУНКЦИИ ДЛЯ HTML ==========
window.completeTask = completeTask;
window.skipTask = skipTask;
window.hideWheelResult = hideWheelResult;
window.changePassword = changePassword;

// ========== ЗАПУСК ВСЕГО ПРИ ЗАГРУЗКЕ СТРАНИЦЫ ==========
document.addEventListener('DOMContentLoaded', () => {
    console.log("DOM загружен");
    initTreeWidget();
    initWheelWidget();
    initCalendar();
    initMobileMenu();
    initProfileMenu();

    // Обработчик для перехода к колесу по клику на задание недели
    const weekTaskBtn = document.querySelector('.week-task-oval');
    if (weekTaskBtn) {
        weekTaskBtn.addEventListener('click', scrollToWheel);
        weekTaskBtn.style.cursor = 'pointer';
    }

    const userLogin = document.getElementById("userLogin");
    const userEmail = document.getElementById("userEmail");
    if (userLogin) userLogin.textContent = localStorage.getItem("userLogin") || "EcoVolunteer";
    if (userEmail) userEmail.textContent = localStorage.getItem("userEmail") || "ecovolunteer@example.com";

    const changePwdBtn = document.getElementById("changePasswordBtn");
    if (changePwdBtn) {
        changePwdBtn.addEventListener("click", changePassword);
    }

    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", () => {
            localStorage.removeItem('userLoggedIn');
            localStorage.removeItem('userLogin');
            localStorage.removeItem('userEmail');
            window.location.href = "vhod.php";
        });
    }
    // Запускаем календарь, когда страница загрузилась
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('calendarDays')) {
            initCalendar();
        }
    });
});