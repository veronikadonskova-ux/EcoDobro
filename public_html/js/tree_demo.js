// ========== ДЕМО-ДЕРЕВО ДЛЯ ГЛАВНОЙ СТРАНИЦЫ ==========
// Автоматически растёт и уменьшается по кругу

const SVG_NS = "http://www.w3.org/2000/svg";

// Стадии роста дерева
const STAGES = [
    " Семечко посажено",
    " Первый росточек",
    " Тянется к солнцу",
    " Появились веточки",
    " Деревце крепнет",
    " Крона растет",
    " Пышная крона",
    " Появились цветы",
    " Зреют яблочки",
    " Яблоня выросла!"
];

const CROWN_COLORS = [
    "#a8d5a2", "#8fca87", "#76bf6c", "#5db451",
    "#4aaa3e", "#3a9f30", "#2e9426", "#228a1c",
    "#167f12", "#0a750a"
];

let currentLevel = 1;
let growthDirection = 1;
let autoInterval = null;
let spinnerAnimation = null;

// Генерация равномерных позиций яблок по кроне
function getApplePositions(cx, crownY, crownSize, level, L) {
    const positions = [];
    const baseRadius = crownSize * 0.9;
    const count = level === 9 ? 8 : (level === 10 ? 14 : 6);
    
    // Определяем возможные углы (смещение, чтобы яблоки не были строго по оси X)
    const angles = [];
    for (let i = 0; i < count; i++) {
        let angle = (i / count) * Math.PI * 2;
        // Добавляем небольшой случайный сдвиг для естественности
        angle += (i * 0.2);
        angles.push(angle);
    }
    
    // Сортируем для предсказуемости, но оставляем равномерное распределение
    angles.sort((a,b) => a - b);
    
    for (let i = 0; i < count; i++) {
        const angle = angles[i];
        // Радиус варьируется, чтобы яблоки были по всей кроне (от центра до края)
        const rVariation = 0.4 + (Math.sin(angle * 2.3) * 0.15);
        const r = baseRadius * (0.5 + rVariation * 0.4);
        
        // Координаты яблока относительно центра кроны
        let x = cx + Math.cos(angle) * r;
        let y = crownY + Math.sin(angle) * r * 0.7; // сжимаем по Y для круглой кроны
        
        // Дополнительная проверка: не слишком ли низко/высоко
        y = Math.min(Math.max(y, crownY - crownSize * 0.7), crownY + crownSize * 0.6);
        x = Math.min(Math.max(x, cx - crownSize * 0.8), cx + crownSize * 0.8);
        
        // Размер яблока зависит от удаленности от центра и уровня
        const size = 4 + (r / baseRadius) * 3 + (L > 9 ? 1 : 0);
        
        positions.push({ x, y, size: Math.min(size, 7) });
    }
    
    // Добавляем несколько яблок ближе к центру для уровня 10
    if (level === 10) {
        const innerCount = 4;
        for (let i = 0; i < innerCount; i++) {
            const angle = (i / innerCount) * Math.PI * 2 + 0.5;
            const r = baseRadius * 0.35;
            let x = cx + Math.cos(angle) * r;
            let y = crownY + Math.sin(angle) * r * 0.6;
            positions.push({ x, y, size: 5 });
        }
    }
    
    return positions;
}

// Рисование дерева
function drawTree(svg, level) {
    if (!svg) return;
    svg.innerHTML = "";
    const L = Math.min(Math.max(level, 1), 10);
    const cx = 150;
    const cyBase = 250;

    // Земля
    const earth = document.createElementNS(SVG_NS, "ellipse");
    earth.setAttribute("cx", cx);
    earth.setAttribute("cy", cyBase);
    earth.setAttribute("rx", 70);
    earth.setAttribute("ry", 10);
    earth.setAttribute("fill", "#8aae7a");
    earth.setAttribute("opacity", "0.4");
    svg.appendChild(earth);

    // Уровень 1: семечко
    if (L === 1) {
        const seed1 = document.createElementNS(SVG_NS, "ellipse");
        seed1.setAttribute("cx", cx);
        seed1.setAttribute("cy", cyBase - 8);
        seed1.setAttribute("rx", 14);
        seed1.setAttribute("ry", 9);
        seed1.setAttribute("fill", "#8B5E3C");
        svg.appendChild(seed1);

        const seed2 = document.createElementNS(SVG_NS, "ellipse");
        seed2.setAttribute("cx", cx + 4);
        seed2.setAttribute("cy", cyBase - 12);
        seed2.setAttribute("rx", 6);
        seed2.setAttribute("ry", 4);
        seed2.setAttribute("fill", "#a07040");
        seed2.setAttribute("opacity", "0.7");
        svg.appendChild(seed2);
        return;
    }

    // Уровень 2: маленький росток
    if (L === 2) {
        const trunk = document.createElementNS(SVG_NS, "rect");
        trunk.setAttribute("x", 148);
        trunk.setAttribute("y", cyBase - 42);
        trunk.setAttribute("width", 4);
        trunk.setAttribute("height", 32);
        trunk.setAttribute("rx", 2);
        trunk.setAttribute("fill", "#7a4f2a");
        svg.appendChild(trunk);

        const crown = document.createElementNS(SVG_NS, "ellipse");
        crown.setAttribute("cx", cx);
        crown.setAttribute("cy", cyBase - 47);
        crown.setAttribute("rx", 12);
        crown.setAttribute("ry", 9);
        crown.setAttribute("fill", CROWN_COLORS[1]);
        svg.appendChild(crown);
        return;
    }

    // Ствол (для уровней 3+)
    const trunkH = 30 + L * 6;
    const trunkW = 8 + L;
    const trunkX = cx - trunkW / 2;
    const trunkY = cyBase - trunkH;

    const trunk = document.createElementNS(SVG_NS, "rect");
    trunk.setAttribute("x", trunkX);
    trunk.setAttribute("y", trunkY);
    trunk.setAttribute("width", trunkW);
    trunk.setAttribute("height", trunkH);
    trunk.setAttribute("rx", trunkW / 2);
    trunk.setAttribute("fill", "#7a4f2a");
    svg.appendChild(trunk);

    // Крона
    const crownSize = 18 + L * 6;
    const crownY = trunkY + 5;
    const color = CROWN_COLORS[L - 1];

    // Главная крона
    const mainCrown = document.createElementNS(SVG_NS, "circle");
    mainCrown.setAttribute("cx", cx);
    mainCrown.setAttribute("cy", crownY);
    mainCrown.setAttribute("r", crownSize);
    mainCrown.setAttribute("fill", color);
    mainCrown.setAttribute("opacity", "0.9");
    svg.appendChild(mainCrown);

    // Боковые ветки (начиная с 4 уровня)
    if (L >= 4) {
        const leftCrown = document.createElementNS(SVG_NS, "circle");
        leftCrown.setAttribute("cx", cx - crownSize * 0.6);
        leftCrown.setAttribute("cy", crownY + crownSize * 0.25);
        leftCrown.setAttribute("r", crownSize * 0.7);
        leftCrown.setAttribute("fill", color);
        leftCrown.setAttribute("opacity", "0.85");
        svg.appendChild(leftCrown);

        const rightCrown = document.createElementNS(SVG_NS, "circle");
        rightCrown.setAttribute("cx", cx + crownSize * 0.6);
        rightCrown.setAttribute("cy", crownY + crownSize * 0.25);
        rightCrown.setAttribute("r", crownSize * 0.7);
        rightCrown.setAttribute("fill", color);
        rightCrown.setAttribute("opacity", "0.85");
        svg.appendChild(rightCrown);
    }

    // Верхняя часть кроны (начиная с 5 уровня)
    if (L >= 5) {
        const topCrown = document.createElementNS(SVG_NS, "circle");
        topCrown.setAttribute("cx", cx);
        topCrown.setAttribute("cy", crownY - crownSize * 0.55);
        topCrown.setAttribute("r", crownSize * 0.65);
        topCrown.setAttribute("fill", color);
        topCrown.setAttribute("opacity", "0.95");
        svg.appendChild(topCrown);
    }

    // Дополнительные ветки (6+ уровень)
    if (L >= 6) {
        const leftTop = document.createElementNS(SVG_NS, "circle");
        leftTop.setAttribute("cx", cx - crownSize * 0.45);
        leftTop.setAttribute("cy", crownY - crownSize * 0.4);
        leftTop.setAttribute("r", crownSize * 0.5);
        leftTop.setAttribute("fill", color);
        leftTop.setAttribute("opacity", "0.85");
        svg.appendChild(leftTop);

        const rightTop = document.createElementNS(SVG_NS, "circle");
        rightTop.setAttribute("cx", cx + crownSize * 0.45);
        rightTop.setAttribute("cy", crownY - crownSize * 0.4);
        rightTop.setAttribute("r", crownSize * 0.5);
        rightTop.setAttribute("fill", color);
        rightTop.setAttribute("opacity", "0.85");
        svg.appendChild(rightTop);
    }

    // Цветочки (начиная с 8 уровня)
    if (L >= 8) {
        const flowerColors = ["#FFB7B2", "#FFDAC1", "#E2F0CB", "#B5EAD7", "#C7CEEA"];
        const flowerPositions = [
            { x: cx - 18, y: crownY - 5, size: 5 },
            { x: cx + 15, y: crownY - 8, size: 5 },
            { x: cx - 8, y: crownY - 25, size: 4 },
            { x: cx + 10, y: crownY - 30, size: 4 },
            { x: cx - 25, y: crownY + 10, size: 4 },
            { x: cx + 22, y: crownY + 8, size: 4 }
        ];
        for (let i = 0; i < Math.min(L - 6, flowerPositions.length); i++) {
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
            center.setAttribute("r", pos.size * 0.4);
            center.setAttribute("fill", "#FFE066");
            svg.appendChild(center);
        }
    }

    // Яблоки (начиная с 8 уровня с цветами, с 9 и 10 — именно яблоки)
    if (L >= 8 && L < 9) {
        // На 8 уровне показываем немного яблок, но в основном цветы
        const previewPositions = getApplePositions(cx, crownY, crownSize, 8, L);
        for (let i = 0; i < Math.min(previewPositions.length, 5); i++) {
            const pos = previewPositions[i];
            const apple = document.createElementNS(SVG_NS, "circle");
            apple.setAttribute("cx", pos.x);
            apple.setAttribute("cy", pos.y);
            apple.setAttribute("r", pos.size * 0.8);
            apple.setAttribute("fill", "#f9a26c");
            apple.setAttribute("stroke", "#e07e3e");
            apple.setAttribute("stroke-width", "1");
            svg.appendChild(apple);
        }
    } else if (L >= 9) {
        const applePositions = getApplePositions(cx, crownY, crownSize, L, L);
        
        for (let i = 0; i < applePositions.length; i++) {
            const pos = applePositions[i];
            
            // Яблоко
            const apple = document.createElementNS(SVG_NS, "circle");
            apple.setAttribute("cx", pos.x);
            apple.setAttribute("cy", pos.y);
            apple.setAttribute("r", pos.size);
            apple.setAttribute("fill", "#e74c3c");
            apple.setAttribute("stroke", "#c0392b");
            apple.setAttribute("stroke-width", "1.5");
            svg.appendChild(apple);

            // Блик
            const highlight = document.createElementNS(SVG_NS, "circle");
            highlight.setAttribute("cx", pos.x - 2);
            highlight.setAttribute("cy", pos.y - 2);
            highlight.setAttribute("r", pos.size * 0.25);
            highlight.setAttribute("fill", "#ff8a7a");
            highlight.setAttribute("opacity", "0.6");
            svg.appendChild(highlight);

            // Листик
            const leaf = document.createElementNS(SVG_NS, "ellipse");
            leaf.setAttribute("cx", pos.x - 3);
            leaf.setAttribute("cy", pos.y - pos.size - 1);
            leaf.setAttribute("rx", 4);
            leaf.setAttribute("ry", 2.5);
            leaf.setAttribute("fill", "#27ae60");
            leaf.setAttribute("transform", `rotate(-30, ${pos.x - 3}, ${pos.y - pos.size - 1})`);
            svg.appendChild(leaf);

            // Плодоножка
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

    // Блик на кроне
    const highlight = document.createElementNS(SVG_NS, "circle");
    highlight.setAttribute("cx", cx - crownSize * 0.3);
    highlight.setAttribute("cy", crownY - crownSize * 0.3);
    highlight.setAttribute("r", crownSize * 0.15);
    highlight.setAttribute("fill", "#ffffff");
    highlight.setAttribute("opacity", "0.12");
    svg.appendChild(highlight);

    // Крутящееся солнце
    if (L < 10) {
        const spinnerGroup = document.createElementNS(SVG_NS, "g");
        spinnerGroup.setAttribute("class", "spinner");
        spinnerGroup.setAttribute("transform", `translate(${cx + 100}, ${crownY - 100})`);

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
                sp.setAttribute("transform", `translate(${cx + 100}, ${crownY - 100}) rotate(${angle})`);
            }
        }, 80);
    } else if (spinnerAnimation) {
        clearInterval(spinnerAnimation);
        spinnerAnimation = null;
    }
}

// Обновление UI
function updateTreeUI() {
    const L = currentLevel;
    
    const stageText = document.getElementById("demoStageText");
    const levelLabel = document.getElementById("demoLevelLabel");
    const progressFill = document.getElementById("demoProgressFill");
    const dotsContainer = document.getElementById("demoLevelDots");
    
    if (stageText) stageText.textContent = STAGES[L - 1];
    if (levelLabel) levelLabel.textContent = `Уровень ${L} из 10`;
    if (progressFill) progressFill.style.width = `${(L / 10) * 100}%`;
    
    if (dotsContainer) {
        dotsContainer.innerHTML = "";
        for (let i = 1; i <= 10; i++) {
            const dot = document.createElement("div");
            dot.className = "dot";
            if (i < L) dot.classList.add("done");
            if (i === L) dot.classList.add("active");
            dotsContainer.appendChild(dot);
        }
    }
    
    const svg = document.getElementById("demoTreeSvg");
    if (svg) drawTree(svg, L);
}

function showDemoMessage(text, isGood = true) {
    const msgDiv = document.getElementById("demoTreeMessage");
    if (msgDiv) {
        msgDiv.textContent = text;
        msgDiv.style.backgroundColor = isGood ? "#e8f5e9" : "#ffebee";
        msgDiv.style.opacity = "1";
        setTimeout(() => {
            msgDiv.style.opacity = "0";
            setTimeout(() => {
                msgDiv.style.display = "none";
                msgDiv.style.opacity = "1";
            }, 500);
        }, 2000);
    }
}

// Автоматическое изменение уровня
function autoChangeLevel() {
    const newLevel = currentLevel + growthDirection;
    
    if (newLevel > 10) {
        growthDirection = -1;
        currentLevel = 9;
    } else if (newLevel < 1) {
        growthDirection = 1;
        currentLevel = 2;
    } else {
        currentLevel = newLevel;
    }
    
    const container = document.getElementById("demoTreeSvgContainer");
    if (container) {
        container.classList.add("pop");
        setTimeout(() => container.classList.remove("pop"), 300);
    }
    
    updateTreeUI();
}

// Запуск демо-дерева
function startDemoTree() {
    if (autoInterval) clearInterval(autoInterval);
    autoInterval = setInterval(autoChangeLevel, 2000);
    console.log("Демо-дерево запущено");
}

// Инициализация демо-дерева
function initDemoTree() {
    const container = document.getElementById("demoTreeWidget");
    if (!container) {
        console.error("demoTreeWidget не найден!");
        return;
    }

    container.innerHTML = `
        <div class="demo-tree-card">
            <div class="demo-tree-container">
                <div class="demo-tree-svg-container" id="demoTreeSvgContainer">
                    <svg id="demoTreeSvg" width="300" height="310" viewBox="0 0 300 310"></svg>
                </div>
            </div>
            <div class="demo-progress-container">
                <div class="demo-dots-container" id="demoLevelDots"></div>
            </div>
        </div>
    `;

    updateTreeUI();
    startDemoTree();
}

// Запускаем при загрузке страницы
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDemoTree);
} else {
    initDemoTree();
}