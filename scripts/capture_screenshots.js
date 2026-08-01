const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');

const EDGE_PATH = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

const executablePath = fs.existsSync(EDGE_PATH) ? EDGE_PATH : CHROME_PATH;
const BASE_URL = 'http://localhost:8080';
const OUTPUT_DIR = path.join(__dirname, '..', 'docs', 'user_manual', 'images');

if (!fs.existsSync(OUTPUT_DIR)) {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

async function submitForm(page, selector) {
    const btn = await page.$(selector);
    if (btn) {
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 8000 }).catch(() => {}),
            btn.click()
        ]);
        await new Promise(r => setTimeout(r, 500));
    }
}

async function run() {
    console.log('Starting browser with executable:', executablePath);
    const browser = await puppeteer.launch({
        executablePath,
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
        defaultViewport: { width: 1280, height: 800 }
    });

    const page = await browser.newPage();

    async function login(email, password) {
        await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle0' });
        await page.type('input[name="email"]', email);
        await page.type('input[name="password"]', password);
        await submitForm(page, 'button[type="submit"]');
    }

    async function logout() {
        await page.goto(`${BASE_URL}/profile`, { waitUntil: 'networkidle0' });
        await submitForm(page, 'form[action$="logout"] button');
    }

    console.log('--- 1. Auth & Multi-Tenant Screenshots ---');

    // 01_login_page.png
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUTPUT_DIR, '01_login_page.png') });

    // 02_login_error_credentials.png (Exception)
    await page.type('input[name="email"]', 'invalid@test.com');
    await page.type('input[name="password"]', 'wrongpass');
    await submitForm(page, 'button[type="submit"]');
    await page.screenshot({ path: path.join(OUTPUT_DIR, '02_login_error_credentials.png') });

    // 03_login_error_empty.png (Exception)
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle0' });
    await page.evaluate(() => {
        document.querySelectorAll('[required]').forEach(el => el.removeAttribute('required'));
    });
    await submitForm(page, 'button[type="submit"]');
    await page.screenshot({ path: path.join(OUTPUT_DIR, '03_login_error_empty.png') });

    // 04_register_page.png
    await page.goto(`${BASE_URL}/register`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUTPUT_DIR, '04_register_page.png') });

    // 05_register_error_duplicate.png (Exception)
    await page.type('input[name="full_name"]', 'Teste Duplicado');
    await page.type('input[name="email"]', 'volunteer@ministry-ops.test');
    await page.type('input[name="password"]', 'password123');
    await submitForm(page, 'button[type="submit"]');
    await page.screenshot({ path: path.join(OUTPUT_DIR, '05_register_error_duplicate.png') });

    // Log in as Volunteer
    await login('volunteer@ministry-ops.test', 'password123');

    // 06_tenant_join_page.png
    await page.goto(`${BASE_URL}/tenant/join`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUTPUT_DIR, '06_tenant_join_page.png') });

    // 07_tenant_join_error_invalid.png (Exception)
    await page.type('input[name="tenant_code"]', 'INVALID_CODE_999');
    await submitForm(page, 'button[type="submit"]');
    await page.screenshot({ path: path.join(OUTPUT_DIR, '07_tenant_join_error_invalid.png') });

    console.log('--- 2. Volunteer Workflows Screenshots ---');

    // 08_volunteer_dashboard.png
    await page.goto(`${BASE_URL}/dashboard`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUTPUT_DIR, '08_volunteer_dashboard.png') });

    // 09_schedule_upcoming.png
    await page.goto(`${BASE_URL}/schedule`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUTPUT_DIR, '09_schedule_upcoming.png') });

    // 10_schedule_confirmed_success.png (Happy Path)
    await submitForm(page, 'form[action$="/schedule/confirm"] button');
    await page.screenshot({ path: path.join(OUTPUT_DIR, '10_schedule_confirmed_success.png') });

    // 11_swaps_marketplace.png
    await page.goto(`${BASE_URL}/swaps`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUTPUT_DIR, '11_swaps_marketplace.png') });

    // 12_swap_created_success.png (Happy Path)
    const select = await page.$('form[action$="/swaps/create"] select[name="assignment_id"]');
    if (select) {
        const optionVal = await select.evaluate(s => s.options[1] ? s.options[1].value : (s.options[0] ? s.options[0].value : ''));
        if (optionVal) {
            await page.select('form[action$="/swaps/create"] select[name="assignment_id"]', optionVal);
            await page.type('form[action$="/swaps/create"] input[name="reason"]', 'Compromisso de trabalho inesperado');
            await submitForm(page, 'form[action$="/swaps/create"] button[type="submit"]');
        }
    }
    await page.screenshot({ path: path.join(OUTPUT_DIR, '12_swap_created_success.png') });

    // 13_checkin_page.png
    await page.goto(`${BASE_URL}/checkin`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUTPUT_DIR, '13_checkin_page.png') });

    // 14_checkin_geofence_error.png (Exception - Distance error)
    await page.evaluate(() => {
        const form = document.querySelector('form[action$="/checkin"]');
        if (form) {
            let latInput = form.querySelector('input[name="latitude"]');
            let lngInput = form.querySelector('input[name="longitude"]');
            if (!latInput) {
                latInput = document.createElement('input');
                latInput.name = 'latitude';
                latInput.type = 'hidden';
                form.appendChild(latInput);
            }
            if (!lngInput) {
                lngInput = document.createElement('input');
                lngInput.name = 'longitude';
                lngInput.type = 'hidden';
                form.appendChild(lngInput);
            }
            latInput.value = '0.00000';
            lngInput.value = '0.00000';
        }
    });
    await submitForm(page, 'form[action$="/checkin"] button[type="submit"]');
    await page.screenshot({ path: path.join(OUTPUT_DIR, '14_checkin_geofence_error.png') });

    // 15_checkin_success.png (Happy Path / Bypass)
    await page.goto(`${BASE_URL}/checkin`, { waitUntil: 'networkidle0' });
    await page.evaluate(() => {
        const form = document.querySelector('form[action$="/checkin"]');
        if (form) {
            let latInput = form.querySelector('input[name="latitude"]');
            let lngInput = form.querySelector('input[name="longitude"]');
            let bypassInput = form.querySelector('input[name="bypass_geofence"]');
            if (!latInput) {
                latInput = document.createElement('input');
                latInput.name = 'latitude';
                latInput.type = 'hidden';
                form.appendChild(latInput);
            }
            if (!lngInput) {
                lngInput = document.createElement('input');
                lngInput.name = 'longitude';
                lngInput.type = 'hidden';
                form.appendChild(lngInput);
            }
            if (!bypassInput) {
                bypassInput = document.createElement('input');
                bypassInput.name = 'bypass_geofence';
                bypassInput.type = 'hidden';
                form.appendChild(bypassInput);
            }
            latInput.value = '-23.55052';
            lngInput.value = '-46.633308';
            bypassInput.value = '1';
        }
    });
    await submitForm(page, 'form[action$="/checkin"] button[type="submit"]');
    await page.screenshot({ path: path.join(OUTPUT_DIR, '15_checkin_success.png') });

    // 16_bulletins_feed.png
    await page.goto(`${BASE_URL}/bulletins`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUTPUT_DIR, '16_bulletins_feed.png') });

    // 17_gamification_leaderboard.png
    await page.goto(`${BASE_URL}/gamification`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUTPUT_DIR, '17_gamification_leaderboard.png') });

    // 18_profile_page.png
    await page.goto(`${BASE_URL}/profile`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUTPUT_DIR, '18_profile_page.png') });

    // 19_volunteer_admin_access_error.png (Exception - Access Denied)
    await page.goto(`${BASE_URL}/admin/dashboard`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUTPUT_DIR, '19_volunteer_admin_access_error.png') });

    console.log('--- 3. Admin & Leader Workflows Screenshots ---');

    await logout();
    await login('admin@ministry-ops.test', 'password123');

    // 20_admin_dashboard.png
    await page.goto(`${BASE_URL}/admin/dashboard`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUTPUT_DIR, '20_admin_dashboard.png') });

    // 21_admin_members.png
    await page.goto(`${BASE_URL}/admin/members`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUTPUT_DIR, '21_admin_members.png') });

    // 22_admin_confirmations.png
    await page.goto(`${BASE_URL}/admin/confirmations`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUTPUT_DIR, '22_admin_confirmations.png') });

    // 23_admin_operations.png
    await page.goto(`${BASE_URL}/admin/operations`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUTPUT_DIR, '23_admin_operations.png') });

    // 24_admin_attendance.png
    await page.goto(`${BASE_URL}/admin/attendance`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUTPUT_DIR, '24_admin_attendance.png') });

    await browser.close();
    console.log('SUCCESS: All 24 screenshots captured successfully!');
}

run().catch(err => {
    console.error('Error capturing screenshots:', err);
    process.exit(1);
});
