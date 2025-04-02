document.addEventListener("DOMContentLoaded", function () {
    const dropdownToggle = document.querySelector(".dropdown-toggle");
    const dropdownMenu = document.querySelector(".dropdown-menu");

    // Función para alternar la visibilidad del menú
    dropdownToggle.addEventListener("click", function (event) {
        event.stopPropagation(); // Evita que el clic se propague y cierre el menú
        dropdownMenu.classList.toggle("show");
    });

    // Cierra el menú si se hace clic fuera
    document.addEventListener("click", function (event) {
        if (!dropdownMenu.contains(event.target) && !dropdownToggle.contains(event.target)) {
            dropdownMenu.classList.remove("show");
        }
    });
});


const languages = {
    es: {
        login: "Iniciar sesión",
        idiomas: "Idiomas ▼",
        boton: "¡Vamos allá!",
        footer: "© 2025 CodeCrafters. Todos los derechos reservados. Todas las marcas registradas pertenecen a sus respectivos dueños en EE. UU. y otros países.",
        privacy: "Política de Privacidad",
        legal: "Información legal",
        cookies: "Cookies",
        about: "A cerca de CodeCrafters"
    },
    en: {
        login: "Login",
        idiomas: "Languages ▼",
        boton: "Let's go!",
        footer: "© 2025 CodeCrafters. All rights reserved. All trademarks are the property of their respective owners in the US and other countries.",
        privacy: "Privacy Policy",
        legal: "Legal Information",
        cookies: "Cookies",
        about: "About CodeCrafters"
    },
    fr: {
        login: "Se connecter",
        idiomas: "Langues ▼",
        boton: "Allons-y!",
        footer: "© 2025 CodeCrafters. Tous droits réservés. Toutes les marques déposées sont la propriété de leurs détenteurs respectifs aux États-Unis et dans d'autres pays.",
        privacy: "Politique de confidentialité",
        legal: "Informations légales",
        cookies: "Cookies",
        about: "À propos de CodeCrafters"
    },
    de: {
        login: "Anmelden",
        idiomas: "Sprachen ▼",
        boton: "Los geht's!",
        footer: "© 2025 CodeCrafters. Alle Rechte vorbehalten.",
        privacy: "Datenschutzrichtlinie",
        legal: "Rechtliche Informationen",
        cookies: "Cookies",
        about: "Über CodeCrafters"
    },
    it: {
        login: "Accedi",
        idiomas: "Lingue ▼",
        boton: "Andiamo!",
        footer: "© 2025 CodeCrafters. Tutti i diritti riservati.",
        privacy: "Informativa sulla privacy",
        legal: "Informazioni legali",
        cookies: "Cookie",
        about: "Informazioni su CodeCrafters"
    },
    pt: {
        login: "Entrar",
        idiomas: "Idiomas ▼",
        boton: "Vamos lá!",
        footer: "© 2025 CodeCrafters. Todos os direitos reservados.",
        privacy: "Política de Privacidade",
        legal: "Informações legais",
        cookies: "Cookies",
        about: "Sobre a CodeCrafters"
    },
    ru: {
        login: "Войти",
        idiomas: "Языки ▼",
        boton: "Поехали!",
        footer: "© 2025 CodeCrafters. Все права защищены.",
        privacy: "Политика конфиденциальности",
        legal: "Правовая информация",
        cookies: "Файлы cookie",
        about: "О CodeCrafters"
    },
    cn: {
        login: "登录",
        idiomas: "语言 ▼",
        boton: "开始吧！",
        footer: "© 2025 CodeCrafters. 保留所有权利。",
        privacy: "隐私政策",
        legal: "法律信息",
        cookies: "Cookies",
        about: "关于 CodeCrafters"
    },
    jp: {
        login: "ログイン",
        idiomas: "言語 ▼",
        boton: "行こう！",
        footer: "© 2025 CodeCrafters. すべての権利を保有。",
        privacy: "プライバシーポリシー",
        legal: "法的情報",
        cookies: "クッキー",
        about: "CodeCraftersについて"
    },
    kr: {
        login: "로그인",
        idiomas: "언어 ▼",
        boton: "시작하자!",
        footer: "© 2025 CodeCrafters. 모든 권리 보유.",
        privacy: "개인정보 보호정책",
        legal: "법률 정보",
        cookies: "쿠키",
        about: "CodeCrafters에 대하여"
    },
    sa: {
        login: "تسجيل الدخول",
        idiomas: "اللغات ▼",
        boton: "لنبدأ!",
        footer: "© 2025 CodeCrafters. جميع الحقوق محفوظة.",
        privacy: "سياسة الخصوصية",
        legal: "المعلومات القانونية",
        cookies: "ملفات تعريف الارتباط",
        about: "عن CodeCrafters"
    },
    in: {
        login: "लॉगिन करें",
        idiomas: "भाषाएँ ▼",
        boton: "चलो चलते हैं!",
        footer: "© 2025 CodeCrafters. सभी अधिकार सुरक्षित।",
        privacy: "गोपनीयता नीति",
        legal: "कानूनी जानकारी",
        cookies: "कुकीज़",
        about: "CodeCrafters के बारे में"
    },
    tr: {
        login: "Giriş Yap",
        idiomas: "Diller ▼",
        boton: "Hadi gidelim!",
        footer: "© 2025 CodeCrafters. Tüm hakları saklıdır.",
        privacy: "Gizlilik Politikası",
        legal: "Yasal Bilgiler",
        cookies: "Çerezler",
        about: "CodeCrafters Hakkında"
    },
    nl: {
        login: "Inloggen",
        idiomas: "Talen ▼",
        boton: "Laten we gaan!",
        footer: "© 2025 CodeCrafters. Alle rechten voorbehouden.",
        privacy: "Privacybeleid",
        legal: "Juridische informatie",
        cookies: "Cookies",
        about: "Over CodeCrafters"
    },
    se: {
        login: "Logga in",
        idiomas: "Språk ▼",
        boton: "Låt oss gå!",
        footer: "© 2025 CodeCrafters. Alla rättigheter förbehållna.",
        privacy: "Integritetspolicy",
        legal: "Juridisk information",
        cookies: "Cookies",
        about: "Om CodeCrafters"
    },
    pl: {
        login: "Zaloguj się",
        idiomas: "Języki ▼",
        boton: "Chodźmy!",
        footer: "© 2025 CodeCrafters. Wszelkie prawa zastrzeżone.",
        privacy: "Polityka prywatności",
        legal: "Informacje prawne",
        cookies: "Ciasteczka",
        about: "O CodeCrafters"
    },
    gr: { 
        login: "Σύνδεση", 
        idiomas: "Γλώσσες ▼", 
        boton: "Πάμε!", 
        footer: "© 2025 CodeCrafters. Όλα τα δικαιώματα διατηρούνται.", 
        privacy: "Πολιτική Απορρήτου", 
        legal: "Νομικές πληροφορίες", 
        cookies: "Cookies", 
        about: "Σχετικά με το CodeCrafters" 
    },
    il: { 
        login: "התחברות", 
        idiomas: "שפות ▼", 
        boton: "בוא נלך!", 
        footer: "© 2025 CodeCrafters. כל הזכויות שמורות.", 
        privacy: "מדיניות פרטיות", 
        legal: "מידע משפטי", 
        cookies: "עוגיות", 
        about: "על CodeCrafters" },
    fi: { 
        login: "Kirjaudu sisään", 
        idiomas: "Kielet ▼", 
        boton: "Mennään!", 
        footer: "© 2025 CodeCrafters. Kaikki oikeudet pidätetään.", 
        privacy: "Tietosuojakäytäntö", 
        legal: "Lailliset tiedot", 
        cookies: "Evästeet", 
        about: "Tietoja CodeCraftersista" 
    },
    dk: { 
        login: "Log ind", 
        idiomas: "Sprog ▼", 
        boton: "Lad os gå!", 
        footer: "© 2025 CodeCrafters. Alle rettigheder forbeholdes.", 
        privacy: "Privatlivspolitik", 
        legal: "Juridiske oplysninger", 
        cookies: "Cookies", 
        about: "Om CodeCrafters" },
    hu: { 
        login: "Bejelentkezés", 
        idiomas: "Nyelvek ▼", 
        boton: "Menjünk!", 
        footer: "© 2025 CodeCrafters. Minden jog fenntartva.", 
        privacy: "Adatvédelmi irányelvek", 
        legal: "Jogi információ", 
        cookies: "Sütik", 
        about: "A CodeCraftersról" 
    },
    cz: { 
        login: "Přihlásit se", 
        idiomas: "Jazyky ▼", 
        boton: "Jdeme na to!", 
        footer: "© 2025 CodeCrafters. Všechna práva vyhrazena.", 
        privacy: "Zásady ochrany osobních údajů", 
        legal: "Právní informace", 
        cookies: "Cookies", 
        about: "O CodeCrafters" 
    },
    ro: { 
        login: "Autentificare", 
        idiomas: "Limbi ▼", 
        boton: "Să mergem!", 
        footer: "© 2025 CodeCrafters. Toate drepturile rezervate.", 
        privacy: "Politica de confidențialitate", 
        legal: "Informații legale", 
        cookies: "Cookies", 
        about: "Despre CodeCrafters" },
    bg: { 
        login: "Вход", 
        idiomas: "Езици ▼", 
        boton: "Да тръгваме!", 
        footer: "© 2025 CodeCrafters. Всички права запазени.", 
        privacy: "Политика за поверителност", 
        legal: "Правна информация", 
        cookies: "Бисквитки", 
        about: "Относно CodeCrafters" 
    },
    ua: { 
        login: "Увійти", 
        idiomas: "Мови ▼", 
        boton: "Поїхали!", 
        footer: "© 2025 CodeCrafters. Усі права захищені.", 
        privacy: "Політика конфіденційності", 
        legal: "Юридична інформація", 
        cookies: "Файли cookie", 
        about: "Про CodeCrafters" 
    },
    th: { 
        login: "เข้าสู่ระบบ", 
        idiomas: "ภาษา ▼", 
        boton: "ไปกันเถอะ!", 
        footer: "© 2025 CodeCrafters. สงวนลิขสิทธิ์ทั้งหมด.", 
        privacy: "นโยบายความเป็นส่วนตัว", 
        legal: "ข้อมูลทางกฎหมาย", 
        cookies: "คุกกี้", 
        about: "เกี่ยวกับ CodeCrafters" },
    id: { 
        login: "Masuk", 
        idiomas: "Bahasa ▼", 
        boton: "Ayo pergi!", 
        footer: "© 2025 CodeCrafters. Semua hak dilindungi.", 
        privacy: "Kebijakan Privasi", 
        legal: "Informasi Hukum", 
        cookies: "Cookies", 
        about: "Tentang CodeCrafters" 
    },
    vn: { 
        login: "Đăng nhập", 
        idiomas: "Ngôn ngữ ▼", 
        boton: "Đi nào!", 
        footer: "© 2025 CodeCrafters. Mọi quyền được bảo lưu.", 
        privacy: "Chính sách Bảo mật", 
        legal: "Thông tin Pháp lý", 
        cookies: "Cookies", 
        about: "Giới thiệu về CodeCrafters" },
    ir: { 
        login: "ورود", 
        idiomas: "زبان ها ▼", 
        boton: "بزن بریم!", 
        footer: "© 2025 CodeCrafters. کلیه حقوق محفوظ است.", 
        privacy: "سیاست حفظ حریم خصوصی", 
        legal: "اطلاعات قانونی", 
        cookies: "کوکی ها", 
        about: "درباره CodeCrafters" 
    }

};

function changeLanguage(lang) {
    // Cambiar el idioma
    if (languages[lang]) {
        localStorage.setItem('language', lang); // Guardar el idioma en localStorage
        
        const elements = document.querySelectorAll('[data-text]');
        elements.forEach(function (element) {
            const textKey = element.getAttribute('data-text');
            if (languages[lang][textKey]) {
                element.innerHTML = languages[lang][textKey];
            }
        });
    }
}

// Al cargar la página, verificar si hay un idioma guardado
window.onload = function () {
    const savedLang = localStorage.getItem('language') || 'es'; // 'es' es el idioma por defecto
    changeLanguage(savedLang);
};



