<?php

// Настройки подключения к базе данных
$host = 'localhost';
$dbname = 'nngasu5804_ecodobro';
$user = 'nngasu5804_ecodobro';
$pass = 'Mu6hgKE5';

$events_db = [];
$carousel_events_db = [];

try {
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    

    $today = date('Y-m-d');

    $sql = "SELECT 
                id, 
                title, 
                category, 
                date, 
                location, 
                time, 
                entry as price, 
                short_description as short_desc, 
                full_description as description, 
                image_url as image, 
                link 
            FROM events 
            WHERE is_active = 1
            ORDER BY date ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $events_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
   
    $sql_carousel = "SELECT 
                        id, 
                        title, 
                        category, 
                        date, 
                        location, 
                        time, 
                        entry as price, 
                        short_description as short_desc, 
                        full_description as description, 
                        image_url as image, 
                        link 
                    FROM events 
                    WHERE is_active = 1 AND date >= :today
                    ORDER BY date ASC 
                    LIMIT 6";
    
    $stmt_carousel = $pdo->prepare($sql_carousel);
    $stmt_carousel->execute(['today' => $today]);
    $carousel_events_db = $stmt_carousel->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    
    $events_db = [];
    $carousel_events_db = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ЭкоДобро - Мероприятия</title>
    <link rel="stylesheet" href="css/css.css">
    <link rel="icon" type="image/png" href="/img/logotype.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Arsenal:wght@400;700&family=Grenze:wght@400;700;800;900&display=swap" rel="stylesheet">
    <style>
       
        .profile-icon-container {
            position: relative;
        }
        .profile-icon {
            width: 50px;
            height: 50px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .profile-icon:hover {
            transform: scale(1.05);
        }
        .profile-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .profile-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 55px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            min-width: 200px;
            z-index: 100;
            overflow: hidden;
        }
        .profile-dropdown.show {
            display: block;
            animation: fadeIn 0.2s ease;
        }
        .dropdown-item {
            display: block;
            padding: 12px 20px;
            color: #5a3b23;
            text-decoration: none;
            transition: background 0.2s;
            font-family: 'Arsenal', sans-serif;
            font-size: 16px;
        }
        .dropdown-item:hover {
            background: #f0f7ea;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="hero-bg">
    <img alt="" src="img/Background.jpg" />
</div>
<div class="gradient-transition"></div>

<div class="content-container">
    <main>
        
    
         <nav class="menu-bg">
            <a href="about.html">О проекте</a>
            <a href="volunteer.php">Волонтерство</a>
            <a href="lichniy-vclad.html">Личный вклад</a>
            <a href="index.html" class="logo-link">
                <img alt="" src="img/logo.png" />
            </a>
            <a href="karta-SOS.html">Карта SOS</a>
            <a href="pynkty-priema.html">Пункты приема</a>
            <a href="events.php">Мероприятия</a>
        </nav>

<div class="container">

    <div class="top-bar">
        <div class="nav-wrapper">
            <a href="index.html" class="breadcrumb">Главная</a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Мероприятия </span>
        </div>
        
     
        <div class="profile-icon-container">
            <div id="profileIcon" class="profile-icon">
                <img src="img/profile.png" alt="Профиль">
            </div>
            <div id="profileDropdown" class="profile-dropdown">
                <a href="lk.php#personal" class="dropdown-item">
                    Личный кабинет
                </a>
                <a href="lk.php#tree" class="dropdown-item">
                    Интерактивное дерево
                </a>
                <a href="lk.php#wheel" class="dropdown-item">
                    Колесо заданий
                </a>
            </div>
        </div>
    </div>    
    
    <h1 class="events-title">МЕРОПРИЯТИЯ</h1>

    <div class="events-carousel-section">
        <div class="events-carousel-container" id="carouselContainer">
            <div class="events-carousel-track" id="carouselTrack"></div>
            <button class="events-nav-btn events-nav-prev" id="prevBtn">‹</button>
            <button class="events-nav-btn events-nav-next" id="nextBtn">›</button>
        </div>
        <div class="indicators" id="indicators" style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;"></div>
    </div>

    <h2 class="events-sub-title">ВСЕ МЕРОПРИЯТИЯ</h2>

    <div class="events-list" id="events-list"></div>

<div class="footer">
    <div class="footer-column">
        <h5>Команда</h5>
        <p>Анна Аксенова<br>Алина Воробьева<br>Вероника Донскова</p>
    </div>
    <div class="footer-column">
        <h5>Информация</h5>
                <p><a href="about.html">О нас</a><br><a href="events.php">Мероприятия</a><br><a href="volunteer.php">Волонтерство</a><br><a href="pynkty-priema.html">Пункты приема</a></p>
    </div>
    <div class="footer-column">
        <h5>Контакты</h5>
        <p>Электронная почта:<br>EcoKindness@gmail.com</p>
    </div>
</div>


<script>
    
    const eventsFromDB = <?php echo json_encode($events_db, JSON_UNESCAPED_UNICODE); ?>;
    const carouselFromDB = <?php echo json_encode($carousel_events_db, JSON_UNESCAPED_UNICODE); ?>;
    
    
    const staticEvents = [
        {
            id: 1,
            anchor: "event1",
            title: "Зеленый Марафон от Сбербанка",
            date: "5 июня 2026 года",
            location: "Спорт Порт стадиона “Нижний Новгород”",
            time: "Начало регистрации в 8:00, торжественное открытие в 10:00",
            price: "Платный (регистрационный взнос)",
            description: `Зелёный Марафон — это традиционный спортивный праздник от Сбера, который ежегодно объединяет тысячи любителей бега и сторонников здорового образа жизни. В 2026 году мероприятие пройдет во Всемирный день окружающей среды, что подчеркивает его экологическую направленность.\n\nЭкологическая и благотворительная составляющая:\nМарафон носит благотворительный характер. Средства от регистрационных взносов и пожертвований направляются в Благотворительный фонд Сбербанка «Вклад в будущее». В разные годы собранные средства шли на поддержку проектов по лесовосстановлению (акция «Сохраним лес»), а также на программы инклюзивного образования для детей с особенностями развития. Участвуя в забеге, вы не только делаете вклад в свое здоровье, но и помогаете важным социальным и экологическим инициативам.`,
            image: "img/зелен.jpg",
            link: "https://greenmarathon.ru"
        },
        {
            id: 2,
            anchor: "event2",
            title: "Благотворительный полумарафон «Беги, герой!»",
            date: "23–24 мая 2026 года",
            location: "Стартовый городок на стадионе “Совкомбанк Арена” (ул. Бетанкура, 1а/1)",
            time: "Двухдневный спортивный праздник",
            price: "2800–3400 руб.",
            description: `«Беги, герой!» — это самый массовый любительский забег Нижегородской области, который ежегодно собирает тысячи участников. В 2025 году на старт вышли 19 000 бегунов, а детский забег стал самым массовым в стране — 4 000 юных участников.\n\nПолумарафон носит благотворительный характер. Часть регистрационных взносов участников формирует ежегодный грант на поддержку социально значимых проектов в регионе. В 2026 году грантовый фонд составляет 750 000 рублей. Эти средства будут направлены на реализацию социального проекта на территории Нижегородской области.\n\nЗа 9 лет существования забега поддержку получили более 15 инициатив: строительство спортивных площадок, экипировка команд из детских домов, оборудование кабинетов ЛФК и адаптивной физкультуры, развитие инклюзивного спорта.\n\nУчастников ждет двухдневный спортивный праздник с выставкой ЭКСПО, развлечениями для всей семьи, камерами хранения, раздевалками и пейсмейкерами на дистанциях. После забега бегуны получают бонусы за медаль в кафе и ресторанах города.`,
            image: "img/беги.jpg",
            link: "https://www.runhero.ru/"
        },
        {
            id: 3,
            anchor: "event3",
            title: "Всероссийская акция «БумБатл»",
            date: "До 15 ноября 2026 года",
            location: "Нижегородская область. Экопункты “Исток”, фандоматы “Экопоинт” (более 10 адресов по Нижнему Новгороду, включая парк «Швейцария» и пр. Гагарина)",
            time: "Акция уже идет!",
            price: "Бесплатный (от 16 кг макулатуры - билет в театр или музей)",
            description: `Хотите спасти деревья и выиграть призы? Участвуйте в «БумБатле»! Это всероссийское соревнование по сбору макулатуры, где предусмотрен командный и личный зачет. В 2025 году нижегородцы собрали 55,5 тонн макулатуры и спасли почти 1000 деревьев. Присоединяйтесь!\n\nДля участия нужно зарегистрироваться на сайте акции, сдать макулатуру (весом от 16 кг, чтобы получить билет в театр или музей), выложить фото чека в личном кабинете. Самые креативные участники, которые опубликуют пост с хештегами #бумбатл, #экосистема, получат подарки.\n\nАкция на 2026 год уже идет! Она продлится до 15 ноября.\n\nСдать макулатуру можно в экопункты «Исток», фандоматы «Экопоинт» (более 10 адресов по Нижнему Новгороду, включая парк «Швейцария» и пр. Гагарина) или в любые другие пункты приема.`,
            image: "img/бумбатл.jpg",
            link: "https://xn--80aba5bc2bd.xn--80aapampemcchfmo7a3c9ehj.xn--p1ai/"
        },
        {
            id: 4,
            anchor: "event4",
            title: "Фестиваль BOTANICA 2026",
            date: "11–12 июля 2026 года",
            location: "Александровский сад, Нижний Новгород",
            time: "Весь день",
            price: "Вход свободный",
            description: `Фестиваль BOTANICA 2026\nЛекторий с экспертами в области экологии и урбанистики, маркет локальных и экологичных событий.\n\nBOTANICA — это ежегодный семейный фестиваль о любви к природе и искусству. В 2026 году он пройдет в формате «Slow Festival». Главная идея — осознанное замедление, гармония с собой и окружающим миром.\n\nГостей ждут живые концерты на открытом воздухе, иммерсивные прогулки по парку, познавательный брендов, а также творческие мастер-классы. Это отличная возможность провести выходные на природе, вдохновиться новыми идеями и найти единомышленников.`,
            image: "img/ботаника.png",
            link: "https://botanicafestival.ru/"
        },
        {
            id: 5,
            anchor: "event5",
            title: "Просветительская программа «Проэко»",
            date: "Середина февраля - апрель 2026 года",
            location: "Дом народного единства + онлайн",
            time: "2 месяца",
            price: "Для школьников и студентов от 14 до 18 лет",
            description: `Просветительская программа «Проэко»\n\nПрограмма «Проэко» от Дома народного единства — это возможность для школьников и студентов от 14 до 18 лет погрузиться в мир социального проектирования и экологических инициатив.\n\nУчастников ждут стратегические сессии, мастер-классы, встречи с экспертами и разработка собственных экопроектов. Лучшие идеи будут реализованы при поддержке правительства региона.\n\nВ рамках программы также открыта фотовыставка, посвященная природе национального парка «Нижегородское Поволжье» (вход свободный).`,
            image: "img/проэко.jpg",
            link: "https://xn----8sbfgbfw2ane3bm.xn--p1ai/special_project/proekt-proeko/"
        },
        {
            id: 6,
            anchor: "event6",
            title: "Всероссийский конкурс «Планета — наше достояние!»",
            date: "До 25 мая 2026 (заочный этап)",
            location: "Онлайн, очный этап - на сайте",
            time: "До июля 2026 года",
            price: "Для школьников и студентов",
            description: `"Решение проблем в сфере экологии – это задача для нашей промышленности и науки, ответственность каждого из нас. Призываю самым активным образом включиться в эту работу и молодёжь. Мы должны передать будущим поколениям экологически благополучную страну, сохранить природный потенциал и заповедный фонд России."\n    В.В. Путин (Из Послания Президента Федеральному Собранию, Москва, 2019)\n\nКонкурс проводится с целью формирования интереса к инновационным экологическим технологиям, повышения знаний о развитии технологий, методов переработки отходов и технологий, снижающих уровень загрязнения окружающей среды, стимулирования самореализации молодёжи в сфере охраны окружающей среды и экологии, а также показать возможности информационной экономики в сохранении окружающей среды.`,
            image: "img/планета.jpg",
            link: "https://nashe-dostoyanie.ru/planeta"
        }
    ];
    
   
    const events = (eventsFromDB.length > 0) ? eventsFromDB : staticEvents;
    const carouselEvents = (carouselFromDB.length > 0) ? carouselFromDB : events.slice(0, 6);
    

    events.forEach((event, index) => {
        if (!event.anchor) {
            event.anchor = `event${event.id || index + 1}`;
        }
    });
    
    carouselEvents.forEach((event, index) => {
        if (!event.anchor) {
            event.anchor = `event${event.id || index + 1}`;
        }
    });

 
    function scrollToEvent(anchorId) {
        const element = document.getElementById(anchorId);
        if (element) {
            const offset = 100;
            const elementPosition = element.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - offset;

            window.scrollTo({
                top: offsetPosition,
                behavior: "smooth"
            });

     
            element.style.transition = "box-shadow 0.3s";
            element.style.boxShadow = "0 0 0 3px #8aae7a, 0 8px 30px rgba(0, 0, 0, 0.2)";
            setTimeout(() => {
                element.style.boxShadow = "";
            }, 1500);
        }
    }

    let currentSlide = 0;

    function renderCarousel() {
        const track = document.getElementById('carouselTrack');
        track.innerHTML = carouselEvents.map((event, index) => `
            <div class="events-carousel-slide" data-event-id="${event.id}" data-anchor="${event.anchor}">
                <div class="events-carousel-card">
                    <img src="${event.image}" alt="${event.title}" style="cursor: pointer;">
                    <div class="events-carousel-card-content">
                        <h3>${event.title}</h3>
                        <p>${(event.short_desc || event.description || '').substring(0, 120)}...</p>
                        <button class="events-btn-more carousel-more-btn" data-anchor="${event.anchor}" style="font-size: 14px; padding: 8px 20px;">Подробнее</button>
                    </div>
                </div>
            </div>
        `).join('');

     
        document.querySelectorAll('.events-carousel-slide img').forEach((img, index) => {
            img.addEventListener('click', () => {
                const anchor = carouselEvents[index].anchor;
                scrollToEvent(anchor);
            });
        });

    
        document.querySelectorAll('.carousel-more-btn').forEach((btn, index) => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const anchor = carouselEvents[index].anchor;
                scrollToEvent(anchor);
            });
        });

        const indicators = document.getElementById('indicators');
        indicators.innerHTML = carouselEvents.map((_, idx) => `
            <button class="indicator" data-index="${idx}" style="width: ${idx === currentSlide ? '24px' : '8px'}; height: 8px; border-radius: 10px; background: ${idx === currentSlide ? '#8aae7a' : '#cbd5e0'}; border: none; cursor: pointer; transition: all 0.3s;"></button>
        `).join('');
    }

    function updateCarousel() {
        const track = document.getElementById('carouselTrack');
        track.style.transform = `translateX(-${currentSlide * 100}%)`;

        document.querySelectorAll('.indicator').forEach((ind, idx) => {
            ind.style.width = idx === currentSlide ? '24px' : '8px';
            ind.style.background = idx === currentSlide ? '#8aae7a' : '#cbd5e0';
        });
    }

    function renderAllEvents() {
        const eventsList = document.getElementById('events-list');
        eventsList.innerHTML = events.map(event => `
            <div class="events-card" id="${event.anchor}">
                <div class="events-image">
                    <img src="${event.image}" alt="${event.title}">
                </div>
                <div class="events-content">
                    <h3 class="event-title">${event.title}</h3>
                    <div class="events-details">
                        <div class="events-detail-item">
                            <span class="events-detail-icon">
                                <img src="img/календарь.png" alt="Дата">
                            </span>
                            <span>${event.date || formatDate(event.date_db)}</span>
                        </div>
                        <div class="events-detail-item">
                            <span class="events-detail-icon">
                                <img src="img/маркер.png" alt="Локация">
                            </span>
                            <span>${event.location}</span>
                        </div>
                        <div class="events-detail-item">
                            <span class="events-detail-icon">
                                <img src="img/время.png" alt="Время">
                            </span>
                            <span>${event.time}</span>
                        </div>
                        <div class="events-detail-item">
                            <span class="events-detail-icon">
                                <img src="img/деньги.png" alt="Стоимость">
                            </span>
                            <span>${event.price}</span>
                        </div>
                    </div>
                    <div class="events-description">
                        ${(event.description || event.full_description || '').split('\n').map(para => `<p>${para}</p>`).join('')}
                    </div>
                    <a href="${event.link}" class="events-btn-more" target="_blank">Подробнее</a>
                </div>
            </div>
        `).join('');
    }
    
    function formatDate(dateStr) {
        if (!dateStr || dateStr === '0000-00-00') return 'Дата уточняется';
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            return `${parts[2]}.${parts[1]}.${parts[0]}`;
        }
        return dateStr;
    }

  
    renderCarousel();
    renderAllEvents();

  
    document.getElementById('prevBtn').addEventListener('click', () => {
        currentSlide = (currentSlide - 1 + carouselEvents.length) % carouselEvents.length;
        updateCarousel();
    });

    document.getElementById('nextBtn').addEventListener('click', () => {
        currentSlide = (currentSlide + 1) % carouselEvents.length;
        updateCarousel();
    });

    document.getElementById('indicators').addEventListener('click', (e) => {
        if (e.target.classList.contains('indicator')) {
            currentSlide = parseInt(e.target.dataset.index);
            updateCarousel();
        }
    });

    

   
    if (window.location.hash) {
        const anchor = window.location.hash.substring(1);
        setTimeout(() => {
            scrollToEvent(anchor);
        }, 500);
    }
</script>
<script src="js/lk.js"></script>
</body>
</html>
