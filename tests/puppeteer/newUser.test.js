// Import the module outside of the hooks
const puppeteer = require("puppeteer");

describe("My Test Suite", () => {
  let browser;
  let page;

  beforeEach(async () => {
    browser = await puppeteer.launch({ headless: false });
    page = await browser.newPage();
  });

  afterEach(async () => {
    await browser.close();
  });
  async function login(page, username, password) {
    await page.goto("https://127.0.0.1:8000/login");
    await page.type("#inputEmail", username);
    await page.type("#inputPassword", password);
    await page.click(".btn-success");
    await page.waitForNavigation();
    await page.waitForSelector("h1");
    // Selektieren der H1-Überschrift
    const h1Text = await page.$eval("h1", (el) => el.textContent);

    // Vergleich des H1-Textes mit dem erwarteten Wert
    expect(h1Text).toBe("Amendment");
  }
  /*
  it("LogIn Testing and chack Headline", async () => {
    await login(page, "J.smit@hotmail.de", "123");
  });
  */
  const delay = (time) => new Promise((resolve) => setTimeout(resolve, time));
  it("Create New User", async () => {
    await login(page, "J.smit@hotmail.de", "123");
    await page.goto("https://127.0.0.1:8000/user/new");

    await page.type("#user_email", "MT");
    await page.type("#user_vorname", "Maximilian");
    await page.type("#user_nachname", "Tustermann");
    await page.type("#user_birthday", "03.09.1989"); // Monat (September = 9)
   
    await page.type("#user_strasse", "Muster Alee");
    await page.type("#user_plz", "19001");
    await page.type("#user_ort", "Münster");
    await page.type("#user_land", "Deutschland");
    await page.click("#user_objekt_0");
    await page.click("#user_company_0");
    await page.click(".btn-success");
    await delay(1000); //
    await page.waitForNavigation();
    const currentUrl = await page.url();
    
    await page.waitForSelector("h1");

    // Selektieren der H1-Überschrift
    const h1Textuser = await page.$eval("h1", (el) => el.textContent);
   
    // Vergleich des H1-Textes mit dem erwarteten Wert
     expect(h1Textuser).toBe("User-Übersicht");
  }, 10000);
});


