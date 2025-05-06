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
        cc: "© 2025 CodeCrafters. Todos los derechos reservados. Todas las marcas registradas pertenecen a sus respectivos dueños en España y otros países. Todos los precios incluyen IVA (donde sea aplicable).",
        privacy: "Política de Privacidad",
        legal: "Información legal",
        cookies: "Cookies",
        about: "A cerca de CodeCrafters"
    },
    en: {
        login: "Login",
        idiomas: "Languages ▼",
        boton: "Let's go!",
        cc: "© 2025 CodeCrafters. All rights reserved. All trademarks are the property of their respective owners in Spain and other countries. All prices include VAT (where applicable).",
        privacy: "Privacy Policy",
        legal: "Legal Information",
        cookies: "Cookies",
        about: "About CodeCrafters"
    },
    fr: {
        login: "Se connecter",
        idiomas: "Langues ▼",
        boton: "Allons-y!",
        cc: "© 2025 CodeCrafters. Tous droits réservés. Toutes les marques déposées sont la propriété de leurs détenteurs respectifs en Espagne et dans d'autres pays. Tous les prix incluent la TVA (lorsque applicable).",
        privacy: "Politique de confidentialité",
        legal: "Informations légales",
        cookies: "Cookies",
        about: "À propos de CodeCrafters"
    },
    de: {
        login: "Anmelden",
        idiomas: "Sprachen ▼",
        cc: "Los geht's!",
        footer: "© 2025 CodeCrafters. Alle Rechte vorbehalten. Alle eingetragenen Marken sind Eigentum ihrer jeweiligen Inhaber in Spanien und anderen Ländern. Alle Preise verstehen sich inklusive MwSt. (sofern zutreffend).",
        privacy: "Datenschutzrichtlinie",
        legal: "Rechtliche Informationen",
        cookies: "Cookies",
        about: "Über CodeCrafters"
    },
    it: {
        login: "Accedi",
        idiomas: "Lingue ▼",
        boton: "Andiamo!",
        cc: "© 2025 CodeCrafters. Tutti i diritti riservati. Tutti i marchi registrati sono di proprietà dei rispettivi titolari in Spagna e in altri paesi. Tutti i prezzi includono l'IVA (dove applicabile).",
        privacy: "Informativa sulla privacy",
        legal: "Informazioni legali",
        cookies: "Cookie",
        about: "Informazioni su CodeCrafters"
    },
    pt: {
        login: "Entrar",
        idiomas: "Idiomas ▼",
        boton: "Vamos lá!",
        cc: "© 2025 CodeCrafters. Todos os direitos reservados. Todas as marcas registradas são propriedade de seus respectivos donos na Espanha e em outros países. Todos os preços incluem IVA (quando aplicável).",
        privacy: "Política de Privacidade",
        legal: "Informações legais",
        cookies: "Cookies",
        about: "Sobre a CodeCrafters"
    },
    ru: {
        login: "Войти",
        idiomas: "Языки ▼",
        boton: "Поехали!",
        cc: "© 2025 CodeCrafters. Все права защищены. Все зарегистрированные товарные знаки являются собственностью их соответствующих владельцев в Испании и других странах. Все цены включают НДС (если применимо).",
        privacy: "Политика конфиденциальности",
        legal: "Правовая информация",
        cookies: "Файлы cookie",
        about: "О CodeCrafters"
    },
    cn: {
        login: "登录",
        idiomas: "语言 ▼",
        boton: "开始吧！",
        cc: "© 2025 CodeCrafters。版权所有。所有注册商标均为其在西班牙及其他国家/地区的各自所有者所有。所有价格均含增值税（如适用）。",
        privacy: "隐私政策",
        legal: "法律信息",
        cookies: "Cookies",
        about: "关于 CodeCrafters"
    },
    jp: {
        login: "ログイン",
        idiomas: "言語 ▼",
        boton: "行こう！",
        cc: "© 2025 CodeCrafters。全著作権所有。すべての登録商標は、スペインおよびその他の国におけるそれぞれの所有者に帰属します。すべての価格には消費税（該当する場合）が含まれています。",
        privacy: "プライバシーポリシー",
        legal: "法的情報",
        cookies: "クッキー",
        about: "CodeCraftersについて"
    },
    kr: {
        login: "로그인",
        idiomas: "언어 ▼",
        boton: "시작하자!",
        cc: "© 2025 CodeCrafters. 모든 권리 보유. 모든 등록 상표는 스페인 및 기타 국가에서 해당 소유자의 자산입니다. 모든 가격에는 부가가치세(VAT)가 포함되어 있습니다(해당되는 경우).",
        privacy: "개인정보 보호정책",
        legal: "법률 정보",
        cookies: "쿠키",
        about: "CodeCrafters에 대하여"
    },
    sa: {
        login: "تسجيل الدخول",
        idiomas: "اللغات ▼",
        boton: "لنبدأ!",
        cc: "© 2025 CodeCrafters. جميع الحقوق محفوظة. جميع العلامات التجارية المسجلة هي ملك لأصحابها في إسبانيا ودول أخرى. جميع الأسعار تشمل ضريبة القيمة المضافة (عند الاقتضاء).",
        privacy: "سياسة الخصوصية",
        legal: "المعلومات القانونية",
        cookies: "ملفات تعريف الارتباط",
        about: "عن CodeCrafters"
    },
    in: {
        login: "लॉगिन करें",
        idiomas: "भाषाएँ ▼",
        boton: "चलो चलते हैं!",
        cc: "© 2025 CodeCrafters. सर्वाधिकार सुरक्षित। सभी पंजीकृत ट्रेडमार्क उनके संबंधित मालिकों के हैं जो स्पेन और अन्य देशों में हैं। सभी मूल्य में वैट (जहां लागू हो) शामिल है।",
        privacy: "गोपनीयता नीति",
        legal: "कानूनी जानकारी",
        cookies: "कुकीज़",
        about: "CodeCrafters के बारे में"
    },
    tr: {
        login: "Giriş Yap",
        idiomas: "Diller ▼",
        boton: "Hadi gidelim!",
        cc: "© 2025 CodeCrafters. Tüm hakları saklıdır. Tüm tescilli markalar, İspanya ve diğer ülkelerdeki ilgili sahiplerine aittir. Tüm fiyatlar KDV dahil olarak belirtilmiştir (uygulanabilir olduğu durumlarda).",
        privacy: "Gizlilik Politikası",
        legal: "Yasal Bilgiler",
        cookies: "Çerezler",
        about: "CodeCrafters Hakkında"
    },
    nl: {
        login: "Inloggen",
        idiomas: "Talen ▼",
        boton: "Laten we gaan!",
        cc: "© 2025 CodeCrafters. Alle rechten voorbehouden. Alle geregistreerde merken zijn eigendom van hun respectieve eigenaren in Spanje en andere landen. Alle prijzen zijn inclusief btw (indien van toepassing).",
        privacy: "Privacybeleid",
        legal: "Juridische informatie",
        cookies: "Cookies",
        about: "Over CodeCrafters"
    },
    se: {
        login: "Logga in",
        idiomas: "Språk ▼",
        boton: "Låt oss gå!",
        cc: "© 2025 CodeCrafters. Alla rättigheter förbehållna. Alla registrerade varumärken tillhör sina respektive ägare i Spanien och andra länder. Alla priser inkluderar moms (där det är tillämpligt).",
        privacy: "Integritetspolicy",
        legal: "Juridisk information",
        cookies: "Cookies",
        about: "Om CodeCrafters"
    },
    pl: {
        login: "Zaloguj się",
        idiomas: "Języki ▼",
        boton: "Chodźmy!",
        cc: "© 2025 CodeCrafters. Wszelkie prawa zastrzeżone. Wszystkie zarejestrowane znaki towarowe są własnością ich odpowiednich właścicieli w Hiszpanii i innych krajach. Wszystkie ceny zawierają VAT (w przypadku jego zastosowania).",
        privacy: "Polityka prywatności",
        legal: "Informacje prawne",
        cookies: "Ciasteczka",
        about: "O CodeCrafters"
    },
    gr: { 
        login: "Σύνδεση", 
        idiomas: "Γλώσσες ▼", 
        boton: "Πάμε!", 
        cc: "© 2025 CodeCrafters. Όλα τα δικαιώματα διατηρούνται. Όλα τα καταχωρημένα εμπορικά σήματα ανήκουν στους αντίστοιχους κατόχους τους στην Ισπανία και σε άλλες χώρες. Όλες οι τιμές περιλαμβάνουν ΦΠΑ (όπου ισχύει).", 
        privacy: "Πολιτική Απορρήτου", 
        legal: "Νομικές πληροφορίες", 
        cookies: "Cookies", 
        about: "Σχετικά με το CodeCrafters" 
    },
    il: { 
        login: "התחברות", 
        idiomas: "שפות ▼", 
        boton: "בוא נלך!", 
        cc: "© 2025 CodeCrafters. כל הזכויות שמורות. כל הסימנים המסחריים הרשומים הם רכושם של בעלי הזכויות המתאימים בספרד ובמדינות אחרות. כל המחירים כוללים מעמ (כאשר זה רלוונטי).", 
        privacy: "מדיניות פרטיות", 
        legal: "מידע משפטי", 
        cookies: "עוגיות", 
        about: "על CodeCrafters" 
    },
    fi: { 
        login: "Kirjaudu sisään", 
        idiomas: "Kielet ▼", 
        boton: "Mennään!", 
        cc: "© 2025 CodeCrafters. Kaikki oikeudet pidätetään. Kaikki rekisteröidyt tavaramerkit ovat niiden asianmukaisten omistajien omaisuutta Espanjassa ja muissa maissa. Kaikki hinnat sisältävät arvonlisäveron (mikäli sovellettavissa).", 
        privacy: "Tietosuojakäytäntö", 
        legal: "Lailliset tiedot", 
        cookies: "Evästeet", 
        about: "Tietoja CodeCraftersista" 
    },
    dk: { 
        login: "Log ind", 
        idiomas: "Sprog ▼", 
        boton: "Lad os gå!", 
        cc: "© 2025 CodeCrafters. Alle rettigheder forbeholdes. Alle registrerede varemærker tilhører deres respektive ejere i Spanien og andre lande. Alle priser er inklusive moms (hvor det er relevant).", 
        privacy: "Privatlivspolitik", 
        legal: "Juridiske oplysninger", 
        cookies: "Cookies", 
        about: "Om CodeCrafters" 
    },
    hu: { 
        login: "Bejelentkezés", 
        idiomas: "Nyelvek ▼", 
        boton: "Menjünk!", 
        cc: "© 2025 CodeCrafters. Minden jog fenntartva. Minden bejegyzett védjegy a megfelelő tulajdonosok birtokában van Spanyolországban és más országokban. Minden ár tartalmazza az ÁFÁ-t (ha alkalmazható).", 
        privacy: "Adatvédelmi irányelvek", 
        legal: "Jogi információ", 
        cookies: "Sütik", 
        about: "A CodeCraftersról" 
    },
    cz: { 
        login: "Přihlásit se", 
        idiomas: "Jazyky ▼", 
        boton: "Jdeme na to!", 
        cc: "© 2025 CodeCrafters. Všechna práva vyhrazena. Všechna registrovaná ochranná známka patří jejich příslušným vlastníkům ve Španělsku a dalších zemích. Všechny ceny zahrnují DPH (pokud je to relevantní).", 
        privacy: "Zásady ochrany osobních údajů", 
        legal: "Právní informace", 
        cookies: "Cookies", 
        about: "O CodeCrafters" 
    },
    ro: { 
        login: "Autentificare", 
        idiomas: "Limbi ▼", 
        boton: "Să mergem!", 
        cc: "© 2025 CodeCrafters. Toate drepturile rezervate. Toate mărcile înregistrate sunt proprietatea titularilor lor respectivi din Spania și alte țări. Toate prețurile includ TVA (acolo unde este cazul).", 
        privacy: "Politica de confidențialitate", 
        legal: "Informații legale", 
        cookies: "Cookies", 
        about: "Despre CodeCrafters" 
    },
    bg: { 
        login: "Вход", 
        idiomas: "Езици ▼", 
        boton: "Да тръгваме!", 
        cc: "© 2025 CodeCrafters. Всички права запазени. Всички регистрирани търговски марки са собственост на съответните им притежатели в Испания и други държави. Всички цени включват ДДС (където е приложимо).", 
        privacy: "Политика за поверителност", 
        legal: "Правна информация", 
        cookies: "Бисквитки", 
        about: "Относно CodeCrafters" 
    },
    ua: { 
        login: "Увійти", 
        idiomas: "Мови ▼", 
        boton: "Поїхали!", 
        cc: "© 2025 CodeCrafters. Усі права захищено. Усі зареєстровані торгові марки є власністю їх відповідних власників в Іспанії та інших країнах. Усі ціни включають ПДВ (де застосовується).", 
        privacy: "Політика конфіденційності", 
        legal: "Юридична інформація", 
        cookies: "Файли cookie", 
        about: "Про CodeCrafters" 
    },
    th: { 
        login: "เข้าสู่ระบบ", 
        idiomas: "ภาษา ▼", 
        boton: "ไปกันเถอะ!", 
        cc: "© 2025 CodeCrafters. สงวนลิขสิทธิ์ทั้งหมด เครื่องหมายการค้าทั้งหมดเป็นทรัพย์สินของเจ้าของที่เกี่ยวข้องในประเทศสเปนและประเทศอื่นๆ ราคาทั้งหมดรวมภาษีมูลค่าเพิ่ม (เมื่อใช้ได้)", 
        privacy: "นโยบายความเป็นส่วนตัว", 
        legal: "ข้อมูลทางกฎหมาย", 
        cookies: "คุกกี้", 
        about: "เกี่ยวกับ CodeCrafters" 
    },
    id: { 
        login: "Masuk", 
        idiomas: "Bahasa ▼", 
        boton: "Ayo pergi!", 
        cc: "© 2025 CodeCrafters. Semua hak dilindungi undang-undang. Semua merek terdaftar adalah milik pemiliknya masing-masing di Spanyol dan negara lainnya. Semua harga sudah termasuk PPN (jika berlaku)", 
        privacy: "Kebijakan Privasi", 
        legal: "Informasi Hukum", 
        cookies: "Cookies", 
        about: "Tentang CodeCrafters" 
    },
    vn: { 
        login: "Đăng nhập", 
        idiomas: "Ngôn ngữ ▼", 
        boton: "Đi nào!", 
        cc: "© 2025 CodeCrafters. Tất cả các quyền được bảo lưu. Tất cả các nhãn hiệu đã đăng ký là tài sản của chủ sở hữu tương ứng tại Tây Ban Nha và các quốc gia khác. Tất cả giá đều bao gồm VAT (nếu có).", 
        privacy: "Chính sách Bảo mật", 
        legal: "Thông tin Pháp lý", 
        cookies: "Cookies", 
        about: "Giới thiệu về CodeCrafters" },
    ir: { 
        login: "ورود", 
        idiomas: "زبان ها ▼", 
        boton: "بزن بریم!", 
        cc: "© 2025 CodeCrafters. کلیه حقوق محفوظ است. تمامی علائم تجاری ثبت شده متعلق به مالکان مربوطه در اسپانیا و سایر کشورهای جهان است. تمامی قیمت‌ها شامل مالیات بر ارزش افزوده (در صورت اعمال) می‌باشد.", 
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


console.log("Cambiando idioma a:", lang);


// Al cargar la página, verificar si hay un idioma guardado
window.onload = function () {
    const savedLang = localStorage.getItem('language') || 'es'; // 'es' es el idioma por defecto
    changeLanguage(savedLang);
};
