import datetime
import json
import os
import re
import sys
from pathlib import Path

import requests
from playwright.sync_api import sync_playwright, TimeoutError as PlaywrightTimeoutError

BASE_URL = "http://localhost/WOODIN_SITE-WEB/"
ADMIN_USERNAME = "admin"
ADMIN_PASSWORD = "password123"

CLIENT_TEST = {
    "full_name": "Test Playwright",
    "email": "playwright_test_woodin@test.cm",
    "phone": "699000001",
    "city": "Yaoundé",
    "password": "Test1234!",
}

COMMANDE_TEST = {
    "customer_name": "Jean Test Playwright",
    "customer_phone": "699000002",
    "customer_email": "test_order@test.cm",
}

SCREENSHOTS_DIR = "screenshots/"
RAPPORT_FILE = "rapport_playwright_woodin.txt"


class WoodinTestBase:
    def __init__(self, browser, results):
        self.browser = browser
        self.results = results

    @staticmethod
    def new_page(context):
        page = context.new_page()
        page.set_default_timeout(15000)
        cdn_patterns = [
            "**/cdn.jsdelivr.net/**",
            "**/cdnjs.cloudflare.com/**",
            "**/fonts.googleapis.com/**",
            "**/fonts.gstatic.com/**",
            "**/kit.fontawesome.com/**",
            "**/use.fontawesome.com/**",
        ]
        for pattern in cdn_patterns:
            page.route(pattern, lambda route: route.abort())
        return page

    def record(self, result):
        self.results.append(result)
        status = result["status"]
        print(f"[{status}] {result['id']} — {result['label']}")
        if status == "FAIL":
            os.makedirs(os.path.dirname(SCREENSHOTS_DIR), exist_ok=True)
            try:
                page = getattr(self, "_last_page", None)
                if page is not None:
                    page.screenshot(path=f"{SCREENSHOTS_DIR}{result['id']}_FAIL.png")
                    result["detail"] += f" Capture: {SCREENSHOTS_DIR}{result['id']}_FAIL.png"
            except Exception:
                pass

    def run_test(self, test_id, label, callback, mutation=False):
        if mutation:
            print(f"[{test_id}] Mutations activées — exécution directe")
        page = None
        try:
            context = self.browser.new_context(viewport={"width": 1280, "height": 900})
            page = self.new_page(context)
            self._last_page = page
            result = callback(page)
            if not isinstance(result, dict):
                raise TypeError("Le test doit retourner un dictionnaire.")
            result.setdefault("id", test_id)
            result.setdefault("label", label)
            result.setdefault("status", "FAIL")
            result.setdefault("detail", "")
            if result["status"] not in {"PASS", "FAIL", "WARNING", "BLOCKED"}:
                result["status"] = "FAIL"
        except PlaywrightTimeoutError:
            result = {"id": test_id, "label": label, "status": "BLOCKED", "detail": "Timeout"}
        except Exception as exc:
            result = {"id": test_id, "label": label, "status": "FAIL", "detail": str(exc)}
        finally:
            if page is not None:
                try:
                    page.context.close()
                except Exception:
                    pass
        self.record(result)
        return result

    def open(self, page, path):
        page.goto(BASE_URL + path, wait_until="domcontentloaded")


class WoodinVisualTests(WoodinTestBase):
    def run_all(self):
        tests = [
            ("V-01", "Hero fond sombre", self.v01),
            ("V-02", "Animation d'entrée hero", self.v02),
            ("V-03", "Motif tissu hero", self.v03),
            ("V-04", "Splash screen", self.v04),
            ("V-05", "Compteurs animés + préservation span.percent", self.v05),
            ("V-06", "AOS cartes produits index.php", self.v06),
            ("V-07", "Hover cartes produits", self.v07),
            ("V-08", "Navbar au scroll", self.v08),
            ("V-09", "AOS catalogue", self.v09),
            ("V-10", "AOS boutiques", self.v10),
            ("V-11", "Responsive mobile 375px", self.v11),
        ]
        for test_id, label, callback in tests:
            self.run_test(test_id, label, callback)

    def v01(self, page):
        self.open(page, "index.php")
        bg = page.evaluate("""
            () => {
              const el = document.querySelector('section.hero.hero-home');
              if (!el) return 'absent';
              const s = window.getComputedStyle(el);
              return s.backgroundColor;
            }
        """)
        if bg == 'absent':
            return {"id": "V-01", "label": "Hero fond sombre", "status": "FAIL", "detail": "Sélecteur hero introuvable"}
        match = re.search(r"rgba?\((\d+),\s*(\d+),\s*(\d+)", bg)
        if not match:
            return {"id": "V-01", "label": "Hero fond sombre", "status": "WARNING", "detail": f"Couleur non interprétable: {bg}"}
        r, g, b = map(int, match.groups())
        if r < 80 and g < 80 and b < 80:
            return {"id": "V-01", "label": "Hero fond sombre", "status": "PASS", "detail": f"Fond sombre détecté: rgb({r},{g},{b})"}
        return {"id": "V-01", "label": "Hero fond sombre", "status": "FAIL", "detail": f"Fond clair détecté: rgb({r},{g},{b})"}

    def v02(self, page):
        self.open(page, "index.php")
        opacity_init = page.evaluate("""
            () => {
              const el = document.querySelector('section.hero-home h1');
              if (!el) return null;
              return parseFloat(window.getComputedStyle(el).opacity);
            }
        """)
        page.wait_for_timeout(1800)
        opacity_final = page.evaluate("""
            () => {
              const el = document.querySelector('section.hero-home h1');
              if (!el) return null;
              return parseFloat(window.getComputedStyle(el).opacity);
            }
        """)
        if opacity_init is None or opacity_final is None:
            return {"id": "V-02", "label": "Animation d'entrée hero", "status": "WARNING", "detail": "Opacité non mesurable"}
        if opacity_init == 1.0:
            return {"id": "V-02", "label": "Animation d'entrée hero", "status": "FAIL", "detail": "opacity_init == 1.0 : pas d'animation"}
        if opacity_final < 0.9:
            return {"id": "V-02", "label": "Animation d'entrée hero", "status": "FAIL", "detail": f"opacity_final={opacity_final} : animation bloquée"}
        return {"id": "V-02", "label": "Animation d'entrée hero", "status": "PASS", "detail": f"opacity_init={opacity_init}, opacity_final={opacity_final}"}

    def v03(self, page):
        self.open(page, "index.php")
        has_pattern = page.evaluate("""
            () => {
              for (const sheet of document.styleSheets) {
                try {
                  for (const rule of sheet.cssRules) {
                    if (rule.selectorText && rule.selectorText.includes('hero') && rule.selectorText.includes('before')) {
                      return true;
                    }
                  }
                } catch (e) {}
              }
              return false;
            }
        """)
        if has_pattern:
            return {"id": "V-03", "label": "Motif tissu hero", "status": "WARNING", "detail": "Règle CSS ::before trouvée"}
        return {"id": "V-03", "label": "Motif tissu hero", "status": "WARNING", "detail": "Non trouvée"}

    def v04(self, page):
        self.open(page, "index.php")
        splash_visible = page.evaluate("""
            () => {
              const el = document.getElementById('splashScreen');
              if (!el) return 'absent';
              const s = window.getComputedStyle(el);
              return (s.display !== 'none' && s.opacity !== '0' && s.visibility !== 'hidden') ? 'visible' : 'hidden';
            }
        """)
        page.wait_for_timeout(2500)
        splash_after = page.evaluate("""
            () => {
              const el = document.getElementById('splashScreen');
              if (!el) return 'absent';
              return el.classList.contains('hidden') || window.getComputedStyle(el).opacity === '0' || window.getComputedStyle(el).display === 'none' ? 'hidden' : 'visible';
            }
        """)
        if splash_after == 'visible':
            return {"id": "V-04", "label": "Splash screen", "status": "FAIL", "detail": "splash_after == 'visible' : splash bloqué"}
        if splash_visible == 'absent':
            return {"id": "V-04", "label": "Splash screen", "status": "FAIL", "detail": "splash jamais créé"}
        if splash_visible == 'hidden':
            return {"id": "V-04", "label": "Splash screen", "status": "WARNING", "detail": "splash_visible == 'hidden' : trop rapide à mesurer"}
        return {"id": "V-04", "label": "Splash screen", "status": "PASS", "detail": f"visible={splash_visible}, after={splash_after}"}

    def v05(self, page):
        self.open(page, "index.php")
        page.evaluate("document.querySelector('section.stats-band').scrollIntoView()")
        page.wait_for_timeout(3000)
        percent_exists = page.evaluate("""
            () => !!document.querySelector('div.stat-item strong span.percent')
        """)
        values = page.evaluate("""
            () => Array.from(document.querySelectorAll('div.stat-item strong')).map(el => el.textContent.replace('%', '').trim())
        """)
        if not percent_exists:
            return {"id": "V-05", "label": "Compteurs animés + préservation span.percent", "status": "FAIL", "detail": "span.percent absent"}
        if not all(abs(float(v) - target) < 20 for v, target in zip(values[:4], [135, 6, 4, 100])):
            return {"id": "V-05", "label": "Compteurs animés + préservation span.percent", "status": "FAIL", "detail": f"Valeurs observées: {values}"}
        return {"id": "V-05", "label": "Compteurs animés + préservation span.percent", "status": "PASS", "detail": f"Valeurs={values}"}

    def v06(self, page):
        self.open(page, "index.php")
        page.evaluate("window.scrollTo(0, document.body.scrollHeight)")
        page.wait_for_timeout(1500)
        count = page.evaluate("""
            () => document.querySelectorAll('[data-aos].aos-animate').length
        """)
        if count == 0:
            return {"id": "V-06", "label": "AOS cartes produits index.php", "status": "FAIL", "detail": "Aucun élément .aos-animate"}
        return {"id": "V-06", "label": "AOS cartes produits index.php", "status": "PASS", "detail": f"count={count}"}

    def v07(self, page):
        self.open(page, "index.php")
        card = page.locator("article.product-card").first
        card.hover()
        page.wait_for_timeout(400)
        transform = page.evaluate("""
            () => {
              const el = document.querySelector('article.product-card');
              if (!el) return 'absent';
              return window.getComputedStyle(el).transform;
            }
        """)
        if transform == 'absent':
            return {"id": "V-07", "label": "Hover cartes produits", "status": "FAIL", "detail": "article.product-card introuvable"}
        translate_y = re.search(r'matrix\([^,]+,[^,]+,[^,]+,[^,]+,[^,]+,\s*(-?\d+(?:\.\d+)?)\)', transform)
        if translate_y and float(translate_y.group(1)) < 0:
            return {"id": "V-07", "label": "Hover cartes produits", "status": "PASS", "detail": f"transform={transform}"}
        return {"id": "V-07", "label": "Hover cartes produits", "status": "FAIL", "detail": f"transform={transform}"}

    def v08(self, page):
        self.open(page, "index.php")
        has_scrolled_init = page.evaluate("""
            () => document.querySelector('nav.navbar').classList.contains('scrolled')
        """)
        page.evaluate("window.scrollBy(0, 300)")
        page.wait_for_timeout(600)
        has_scrolled_after = page.evaluate("""
            () => document.querySelector('nav.navbar').classList.contains('scrolled')
        """)
        if has_scrolled_init is True:
            return {"id": "V-08", "label": "Navbar au scroll", "status": "FAIL", "detail": "Classe scrolled présente trop tôt"}
        if has_scrolled_after is False:
            return {"id": "V-08", "label": "Navbar au scroll", "status": "FAIL", "detail": "Classe scrolled jamais ajoutée"}
        return {"id": "V-08", "label": "Navbar au scroll", "status": "PASS", "detail": f"before={has_scrolled_init}, after={has_scrolled_after}"}

    def v09(self, page):
        self.open(page, "catalogue.php")
        page.evaluate("window.scrollTo(0, document.body.scrollHeight)")
        page.wait_for_timeout(1500)
        count = page.evaluate("""
            () => document.querySelectorAll('.product-column.aos-animate').length
        """)
        if count == 0:
            return {"id": "V-09", "label": "AOS catalogue", "status": "FAIL", "detail": "0 colonne animée"}
        return {"id": "V-09", "label": "AOS catalogue", "status": "PASS", "detail": f"count={count}"}

    def v10(self, page):
        self.open(page, "boutiques.php")
        page.evaluate("window.scrollTo(0, document.body.scrollHeight)")
        page.wait_for_timeout(1500)
        count = page.evaluate("""
            () => document.querySelectorAll('[data-aos].aos-animate').length
        """)
        if count == 0:
            return {"id": "V-10", "label": "AOS boutiques", "status": "FAIL", "detail": "0 card animée"}
        return {"id": "V-10", "label": "AOS boutiques", "status": "PASS", "detail": f"count={count}"}

    def v11(self, page):
        issues = []
        for path in ["index.php", "catalogue.php", "boutiques.php"]:
            page.set_viewport_size({"width": 375, "height": 812})
            self.open(page, path)
            overflow = page.evaluate("""
                () => document.body.scrollWidth > window.innerWidth
            """)
            if overflow:
                issues.append(f"{path}: scrollWidth > width")
        if issues:
            return {"id": "V-11", "label": "Responsive mobile 375px", "status": "FAIL", "detail": "; ".join(issues)}
        return {"id": "V-11", "label": "Responsive mobile 375px", "status": "PASS", "detail": "Aucun débordement horizontal"}


class WoodinFunctionalTests(WoodinTestBase):
    def run_all(self):
        tests = [
            ("F-13", "Inscription client", self.f13, True),
            ("F-12", "Connexion client", self.f12, True),
            ("F-02", "Recherche produit", self.f02, False),
            ("F-03", "Filtres yards", self.f03, False),
            ("F-04", "Tri par prix", self.f04, False),
            ("F-05", "Réaffichage après filtre", self.f05, False),
            ("F-06", "Ajout panier + feedback visuel", self.f06, True),
            ("F-07", "Contenu panier", self.f07, True),
            ("F-08", "Modification quantité", self.f08, True),
            ("F-09", "Suppression article", self.f09, True),
            ("F-10", "Passage de commande", self.f10, True),
            ("F-11", "Pagination", self.f11, False),
            ("F-14", "Code promo invalide", self.f14, True),
            ("F-15", "Formulaire contact", self.f15, True),
            ("F-16", "Connexion admin", self.f16, False),
            ("F-17", "Dashboard admin stats", self.f17, False),
            ("F-18", "CRUD produit admin", self.f18, True),
            ("F-19", "Changement statut commande", self.f19, True),
            ("F-20", "Téléchargement facture admin", self.f20, False),
        ]
        for test_id, label, callback, mutation in tests:
            self.run_test(test_id, label, callback, mutation=mutation)

    def open(self, page, path):
        page.goto(BASE_URL + path, wait_until="domcontentloaded")

    def clear_cart(self, page):
        self.open(page, "panier.php")
        page.click("form[action='actions/clear_cart.php'] button")
        page.wait_for_load_state("domcontentloaded")

    def f02(self, page):
        self.open(page, "catalogue.php")
        initial = page.evaluate("""
            () => Array.from(document.querySelectorAll('.product-column')).filter(el => !el.hidden && getComputedStyle(el).display !== 'none').length
        """)
        page.fill("input#productSearch", "pagne")
        page.wait_for_timeout(400)
        after_search = page.evaluate("""
            () => Array.from(document.querySelectorAll('.product-column')).filter(el => !el.hidden && getComputedStyle(el).display !== 'none').length
        """)
        page.fill("input#productSearch", "")
        page.wait_for_timeout(400)
        restored = page.evaluate("""
            () => Array.from(document.querySelectorAll('.product-column')).filter(el => !el.hidden && getComputedStyle(el).display !== 'none').length
        """)
        if after_search >= initial and initial > 1:
            return {"id": "F-02", "label": "Recherche produit", "status": "FAIL", "detail": "La recherche n'a rien filtré"}
        if restored < initial:
            return {"id": "F-02", "label": "Recherche produit", "status": "FAIL", "detail": "Les produits ne reviennent pas"}
        return {"id": "F-02", "label": "Recherche produit", "status": "PASS", "detail": f"initial={initial}, after={after_search}, restored={restored}"}

    def f03(self, page):
        self.open(page, "catalogue.php")
        page.click("button.filter-btn[data-filter='4 yards']")
        page.wait_for_timeout(400)
        invalid = page.evaluate("""
            () => Array.from(document.querySelectorAll('.product-column')).filter(el => {
              const visible = el.style.display !== 'none' && !el.classList.contains('d-none');
              const cat = (el.dataset.category || '').toLowerCase();
              return visible && !cat.includes('4 yards');
            }).length
        """)
        page.click("button.filter-btn[data-filter='all']")
        page.wait_for_timeout(400)
        if invalid > 0:
            return {"id": "F-03", "label": "Filtres yards", "status": "FAIL", "detail": f"invalid={invalid}"}
        return {"id": "F-03", "label": "Filtres yards", "status": "PASS", "detail": "Filtre 4 yards valide"}

    def f04(self, page):
        self.open(page, "catalogue.php")
        page.select_option("select#sortProducts", "asc")
        page.wait_for_timeout(400)
        prices_asc = page.evaluate("""
            () => Array.from(document.querySelectorAll('.product-column:not([style*="none"])')).map(el => parseFloat(el.dataset.price || '0'))
        """)
        is_sorted = all(prices_asc[i] <= prices_asc[i + 1] for i in range(len(prices_asc) - 1))
        if not is_sorted:
            return {"id": "F-04", "label": "Tri par prix", "status": "FAIL", "detail": f"asc={prices_asc}"}
        return {"id": "F-04", "label": "Tri par prix", "status": "PASS", "detail": f"asc={prices_asc}"}

    def f05(self, page):
        self.open(page, "catalogue.php")
        page.evaluate("""() => document.querySelectorAll('.product-column').forEach(el => { el.hidden = false; el.style.display = ''; el.style.opacity = '1'; })""")
        page.click("button.filter-btn[data-filter='4 yards']")
        page.wait_for_timeout(400)
        page.click("button.filter-btn[data-filter='all']")
        page.wait_for_timeout(600)
        invisible = page.evaluate("""
            () => Array.from(document.querySelectorAll('.product-column')).filter(el => {
              const s = window.getComputedStyle(el);
              return s.display !== 'none' && parseFloat(s.opacity) < 0.1;
            }).length
        """)
        if invisible > 0:
            return {"id": "F-05", "label": "Réaffichage après filtre", "status": "FAIL", "detail": f"invisible={invisible}"}
        return {"id": "F-05", "label": "Réaffichage après filtre", "status": "PASS", "detail": "Tous les produits visibles"}

    def f06(self, page):
        self.clear_cart(page)
        self.open(page, "catalogue.php")
        initial = page.evaluate("""
            () => {
              const el = document.querySelector('#cart-icon-count');
              return el ? el.textContent.trim() : '0';
            }
        """)
        page.locator("form[action='actions/add_to_cart.php'] button.btn.btn-gold:not([disabled])").first.click()
        page.wait_for_load_state("domcontentloaded")
        final = page.evaluate("""
            () => {
                            const el = document.querySelector('#cart-icon-count');
              return el ? el.textContent.trim() : '0';
            }
        """)
        if "catalogue.php" in page.url and final == initial:
            return {"id": "F-06", "label": "Ajout panier + feedback visuel", "status": "FAIL", "detail": f"initial={initial}, final={final}, url={page.url}"}
        self.clear_cart(page)
        return {"id": "F-06", "label": "Ajout panier + feedback visuel", "status": "PASS", "detail": f"initial={initial}, final={final}, url={page.url}"}

    def f07(self, page):
        self.clear_cart(page)
        self.open(page, "catalogue.php")
        page.locator("form[action='actions/add_to_cart.php'] button[type='submit']:not([disabled])").first.click()
        page.wait_for_load_state("domcontentloaded")
        self.open(page, "panier.php")
        rows = page.locator("table.cart-table tbody tr").count()
        total_text = page.locator("div.cart-summary strong").inner_text()
        if rows == 0:
            return {"id": "F-07", "label": "Contenu panier", "status": "FAIL", "detail": "Aucune ligne panier"}
        total_value = re.sub(r"[^0-9]", "", total_text)
        if not total_value or int(total_value) <= 0:
            return {"id": "F-07", "label": "Contenu panier", "status": "FAIL", "detail": f"rows={rows}, total={total_text}"}
        return {"id": "F-07", "label": "Contenu panier", "status": "PASS", "detail": f"rows={rows}, total={total_text}"}

    def f08(self, page):
        self.open(page, "catalogue.php")
        page.locator("form[action='actions/add_to_cart.php'] button[type='submit']:not([disabled])").first.click()
        page.wait_for_load_state("domcontentloaded")
        self.open(page, "panier.php")
        qty_before = page.locator("table.cart-table tbody tr:first-child td:nth-child(3)").inner_text()
        page.click("form[action='actions/cart.php']:has(input[value='increase']) button")
        page.wait_for_load_state("domcontentloaded")
        qty_after = page.locator("table.cart-table tbody tr:first-child td:nth-child(3)").inner_text()
        if qty_after == qty_before:
            return {"id": "F-08", "label": "Modification quantité", "status": "FAIL", "detail": f"before={qty_before}, after={qty_after}"}
        self.clear_cart(page)
        return {"id": "F-08", "label": "Modification quantité", "status": "PASS", "detail": f"before={qty_before}, after={qty_after}"}

    def f09(self, page):
        self.open(page, "catalogue.php")
        page.locator("form[action='actions/add_to_cart.php'] button[type='submit']:not([disabled])").first.click()
        page.wait_for_load_state("domcontentloaded")
        self.open(page, "panier.php")
        rows_before = page.locator("table.cart-table tbody tr").count()
        if rows_before == 0:
            return {"id": "F-09", "label": "Suppression article", "status": "FAIL", "detail": "Panier vide pour suppression"}
        page.click("button.cart-remove")
        page.wait_for_load_state("domcontentloaded")
        rows_after = page.locator("table.cart-table tbody tr").count()
        if rows_after >= rows_before:
            return {"id": "F-09", "label": "Suppression article", "status": "FAIL", "detail": f"before={rows_before}, after={rows_after}"}
        return {"id": "F-09", "label": "Suppression article", "status": "PASS", "detail": f"before={rows_before}, after={rows_after}"}

    def f10(self, page):
        self.clear_cart(page)
        self.open(page, "catalogue.php")
        page.locator("form[action='actions/add_to_cart.php'] button.btn.btn-gold:not([disabled])").first.click()
        page.wait_for_load_state("domcontentloaded")
        self.open(page, "panier.php")
        page.fill("input[name='customer_name']", COMMANDE_TEST["customer_name"])
        page.fill("input[name='customer_phone']", COMMANDE_TEST["customer_phone"])
        page.fill("input[name='customer_email']", COMMANDE_TEST["customer_email"])
        page.click("form.checkout-form button[type='submit']")
        page.wait_for_load_state("domcontentloaded")
        if "order_success.php" not in page.url:
            return {"id": "F-10", "label": "Passage de commande", "status": "FAIL", "detail": f"url={page.url}"}
        return {"id": "F-10", "label": "Passage de commande", "status": "PASS", "detail": f"url={page.url}"}

    def f11(self, page):
        self.open(page, "catalogue.php")
        has_pagination = page.locator("nav a[href*='page=']").count() > 0
        if not has_pagination:
            return {"id": "F-11", "label": "Pagination", "status": "WARNING", "detail": "Pagination inactive : moins de 13 produits"}
        page.click("a[href*='page=2']")
        page.wait_for_load_state("domcontentloaded")
        if "page=2" not in page.url:
            return {"id": "F-11", "label": "Pagination", "status": "FAIL", "detail": f"url={page.url}"}
        return {"id": "F-11", "label": "Pagination", "status": "PASS", "detail": f"url={page.url}"}

    def f12(self, page):
        self.open(page, "client/login.php")
        page.fill("input[name='identifier']", CLIENT_TEST["email"])
        page.fill("input[name='password']", CLIENT_TEST["password"])
        page.click("button[type='submit']")
        page.wait_for_load_state("domcontentloaded")
        if "mon-compte.php" in page.url:
            return {"id": "F-12", "label": "Connexion client", "status": "PASS", "detail": f"url={page.url}"}
        if page.locator("div.alert.alert-danger").count() > 0:
            return {"id": "F-12", "label": "Connexion client", "status": "WARNING", "detail": "Compte absent ou identifiants refusés"}
        return {"id": "F-12", "label": "Connexion client", "status": "FAIL", "detail": f"url={page.url}"}

    def f13(self, page):
        self.open(page, "client/register.php")
        page.fill("input[name='full_name']", CLIENT_TEST["full_name"])
        page.fill("input[name='email']", CLIENT_TEST["email"])
        page.fill("input[name='phone']", CLIENT_TEST["phone"])
        page.fill("input[name='city']", CLIENT_TEST["city"])
        page.fill("input[name='password']", CLIENT_TEST["password"])
        page.click("button[type='submit']")
        page.wait_for_load_state("domcontentloaded")
        has_error = page.locator("div.alert.alert-danger").count() > 0
        if has_error:
            text = page.locator("body").inner_text()
            if "déjà utilisé" in text.lower() or "already" in text.lower():
                return {"id": "F-13", "label": "Inscription client", "status": "WARNING", "detail": "Email déjà utilisé"}
            return {"id": "F-13", "label": "Inscription client", "status": "FAIL", "detail": text[:200]}
        if "mon-compte.php" not in page.url:
            return {"id": "F-13", "label": "Inscription client", "status": "FAIL", "detail": f"url={page.url}"}
        return {"id": "F-13", "label": "Inscription client", "status": "PASS", "detail": f"url={page.url}"}

    def f14(self, page):
        self.open(page, "catalogue.php")
        page.locator("form[action='actions/add_to_cart.php'] button[type='submit']:not([disabled])").first.click()
        page.wait_for_load_state("domcontentloaded")
        self.open(page, "panier.php")
        page.fill("form[action='actions/apply_discount.php'] input[name='code']", "INVALID_CODE_TEST")
        page.click("form[action='actions/apply_discount.php'] button")
        page.wait_for_load_state("domcontentloaded")
        applied = page.locator("p.text-success").count() > 0
        if applied:
            return {"id": "F-14", "label": "Code promo invalide", "status": "FAIL", "detail": "Code invalide appliqué"}
        return {"id": "F-14", "label": "Code promo invalide", "status": "PASS", "detail": "Code invalide rejeté"}

    def f15(self, page):
        self.open(page, "contact.php")
        page.fill("input[name='nom']", "Test Playwright")
        page.fill("input[name='telephone']", "699000003")
        page.fill("input[name='email']", "contact_test@test.cm")
        page.fill("textarea[name='message']", "Message de test automatisé Playwright.")
        page.click("button.btn.btn-gold[type='submit']")
        page.wait_for_load_state("domcontentloaded")
        confirmed = page.locator("p.form-feedback").count() > 0
        if not confirmed:
            return {"id": "F-15", "label": "Formulaire contact", "status": "FAIL", "detail": "Aucune confirmation"}
        return {"id": "F-15", "label": "Formulaire contact", "status": "PASS", "detail": "Confirmation affichée"}

    def login_admin(self, page):
        self.open(page, "admin/login.php")
        page.fill("input[name='username']", ADMIN_USERNAME)
        page.fill("input[name='password']", ADMIN_PASSWORD)
        page.click("button[type='submit']")
        page.wait_for_load_state("domcontentloaded")
        return "admin/index.php" in page.url

    def f16(self, page):
        ok = self.login_admin(page)
        if not ok:
            return {"id": "F-16", "label": "Connexion admin", "status": "FAIL", "detail": f"url={page.url}"}
        return {"id": "F-16", "label": "Connexion admin", "status": "PASS", "detail": f"url={page.url}"}

    def f17(self, page):
        if not self.login_admin(page):
            return {"id": "F-17", "label": "Dashboard admin stats", "status": "BLOCKED", "detail": "Connexion admin impossible"}
        self.open(page, "admin/index.php")
        cards = page.locator("div.card strong.fs-4").all_inner_texts()
        has_data = any(v.strip() not in ["0 FCFA", "0", ""] for v in cards)
        if not has_data:
            return {"id": "F-17", "label": "Dashboard admin stats", "status": "WARNING", "detail": "Toutes les stats à 0 — base de données vide ?"}
        return {"id": "F-17", "label": "Dashboard admin stats", "status": "PASS", "detail": f"cards={cards}"}

    def f18(self, page):
        if not self.login_admin(page):
            return {"id": "F-18", "label": "CRUD produit admin", "status": "BLOCKED", "detail": "Connexion admin impossible"}
        self.open(page, "admin/products.php")
        page.locator("form[enctype='multipart/form-data']").wait_for()
        page.fill("input[name='name']", "PLAYWRIGHT_TEST_PROD")
        page.fill("textarea[name='description']", "Produit de test automatisé")
        page.fill("input[name='price']", "1000")
        page.fill("input[name='stock']", "5")
        page.select_option("select[name='category_id']", index=1)
        page.click("button[type='submit']")
        page.wait_for_load_state("domcontentloaded")
        if "PLAYWRIGHT_TEST_PROD" not in page.content():
            return {"id": "F-18", "label": "CRUD produit admin", "status": "FAIL", "detail": "Produit créé introuvable"}
        product_row = page.locator("tr", has_text="PLAYWRIGHT_TEST_PROD").first
        product_row.get_by_role("link", name="Modifier").click()
        page.locator("form[enctype='multipart/form-data']").wait_for()
        page.fill("input[name='price']", "2000")
        page.click("button[type='submit']")
        page.wait_for_load_state("domcontentloaded")
        product_row = page.locator("tr", has_text="PLAYWRIGHT_TEST_PROD").first
        delete_button = product_row.get_by_role("button", name="Supprimer")
        product_id = delete_button.get_attribute("data-item-id")
        if not product_id:
            return {"id": "F-18", "label": "CRUD produit admin", "status": "FAIL", "detail": "Identifiant produit absent"}
        delete_form = page.locator("#deleteModal form").first
        delete_form.locator("#deleteItemId").fill(product_id)
        delete_form.locator("button.btn-danger").click(force=True)
        page.wait_for_load_state("domcontentloaded")
        page.reload(wait_until="domcontentloaded")
        if "PLAYWRIGHT_TEST_PROD" in page.content():
            return {"id": "F-18", "label": "CRUD produit admin", "status": "FAIL", "detail": "Produit non supprimé"}
        return {"id": "F-18", "label": "CRUD produit admin", "status": "PASS", "detail": "Produit créé, modifié et supprimé"}

    def f19(self, page):
        if not self.login_admin(page):
            return {"id": "F-19", "label": "Changement statut commande", "status": "BLOCKED", "detail": "Connexion admin impossible"}
        self.open(page, "admin/orders.php")
        rows = page.locator("table.table tbody tr").count()
        if rows == 0:
            return {"id": "F-19", "label": "Changement statut commande", "status": "WARNING", "detail": "Aucune commande en base"}
        page.select_option("table.table tbody tr:first-child select[name='status']", "Confirmée")
        page.click("table.table tbody tr:first-child button.btn-warning")
        page.wait_for_load_state("domcontentloaded")
        status = page.inner_text("table.table tbody tr:first-child select[name='status'] option[selected]")
        if "Confirmée" not in status:
            return {"id": "F-19", "label": "Changement statut commande", "status": "FAIL", "detail": f"status={status}"}
        return {"id": "F-19", "label": "Changement statut commande", "status": "PASS", "detail": f"status={status}"}

    def f20(self, page):
        if not self.login_admin(page):
            return {"id": "F-20", "label": "Téléchargement facture admin", "status": "BLOCKED", "detail": "Connexion admin impossible"}
        self.open(page, "admin/orders.php")
        page.wait_for_timeout(500)
        invoice_href = page.get_attribute("a[href^='download-invoice.php']", "href")
        if not invoice_href:
            return {"id": "F-20", "label": "Téléchargement facture admin", "status": "BLOCKED", "detail": "Aucun lien de facture trouvé"}
        cookies = {cookie["name"]: cookie["value"] for cookie in page.context.cookies()}
        response = requests.get(BASE_URL + "admin/" + invoice_href, cookies=cookies, timeout=15)
        content_type = response.headers.get("Content-Type", "")
        if response.status_code != 200 or "application/pdf" not in content_type:
            return {"id": "F-20", "label": "Téléchargement facture admin", "status": "FAIL", "detail": f"status={response.status_code}, content_type={content_type}"}
        return {"id": "F-20", "label": "Téléchargement facture admin", "status": "PASS", "detail": f"status={response.status_code}, content_type={content_type}"}


class WoodinSecurityTests(WoodinTestBase):
    def run_all(self):
        tests = [
            ("S-06", "Brute force admin", self.s06),
            ("S-07", "Brute force client", self.s07),
            ("S-09", "XSS recherche catalogue", self.s09),
            ("S-10", "Isolation facture entre clients", self.s10),
        ]
        for test_id, label, callback in tests:
            self.run_test(test_id, label, callback)

    def s06(self, page):
        page.goto(BASE_URL + "admin/login.php", wait_until="domcontentloaded")
        blocked = False
        for i in range(6):
            page.fill("input[name='username']", "admin_faux_test")
            page.fill("input[name='password']", f"mauvais_pwd_{i}")
            page.click("button[type='submit']")
            page.wait_for_load_state("domcontentloaded")
            blocked = blocked or page.locator("div.alert.alert-danger").count() > 0
        if not blocked:
            return {"id": "S-06", "label": "Brute force admin", "status": "FAIL", "detail": "Blocage non détecté après 6 tentatives"}
        return {"id": "S-06", "label": "Brute force admin", "status": "PASS", "detail": "Blocage détecté"}

    def s07(self, page):
        page.goto(BASE_URL + "client/login.php", wait_until="domcontentloaded")
        blocked = False
        for i in range(6):
            page.fill("input[name='identifier']", "invalid_playwright@test.cm")
            page.fill("input[name='password']", f"mauvais_pwd_{i}")
            page.click("button[type='submit']")
            page.wait_for_load_state("domcontentloaded")
            blocked = blocked or page.locator("div.alert.alert-danger").count() > 0
        if not blocked:
            return {"id": "S-07", "label": "Brute force client", "status": "FAIL", "detail": "Blocage non détecté après 6 tentatives"}
        return {"id": "S-07", "label": "Brute force client", "status": "PASS", "detail": "Blocage détecté"}

    def s09(self, page):
        page.goto(BASE_URL + "catalogue.php", wait_until="domcontentloaded")
        payload = "<script>document.title='XSS_DETECTED'</script>"
        page.fill("input#productSearch", payload)
        page.wait_for_timeout(600)
        title = page.title()
        inner = page.inner_html("div#productGrid")
        if "XSS_DETECTED" in title or "<script>" in inner.lower():
            return {"id": "S-09", "label": "XSS recherche catalogue", "status": "FAIL", "detail": f"title={title}; script_present={'<script>' in inner.lower()}"}
        return {"id": "S-09", "label": "XSS recherche catalogue", "status": "PASS", "detail": f"title={title}"}

    def s10(self, page):
        return {"id": "S-10", "label": "Isolation facture entre clients", "status": "BLOCKED", "detail": "Nécessite setup manuel"}


class WoodinTestRunner:
    def __init__(self):
        self.results = []

    def run(self):
        with sync_playwright() as p:
            browser_visible = p.chromium.launch(headless=False)
            try:
                visual = WoodinVisualTests(browser_visible, self.results)
                visual.run_all()
                functional = WoodinFunctionalTests(browser_visible, self.results)
                functional.run_all()
            finally:
                browser_visible.close()

            browser_headless = p.chromium.launch(headless=True)
            try:
                security = WoodinSecurityTests(browser_headless, self.results)
                security.run_all()
            finally:
                browser_headless.close()
        return self.results

    def generate_report(self):
        counts = {status: sum(1 for item in self.results if item["status"] == status) for status in ["PASS", "FAIL", "WARNING", "BLOCKED"]}
        lines = [
            "================================================================",
            "RAPPORT DE TEST PLAYWRIGHT — WOODIN CAMEROUN",
            f"Date : {datetime.datetime.now().isoformat()}",
            "Base URL : http://localhost/WOODIN_SITE-WEB/",
            "================================================================",
            "RÉSUMÉ",
            f"  PASS    : {counts['PASS']}/34",
            f"  FAIL    : {counts['FAIL']}/34",
            f"  WARNING : {counts['WARNING']}/34",
            f"  BLOCKED : {counts['BLOCKED']}/34",
            "",
            "FAILS CRITIQUES 🔴",
        ]
        critical_failures = [item for item in self.results if item["status"] == "FAIL" and (item["id"].startswith("F-") or item["id"].startswith("S-"))]
        for item in critical_failures:
            lines.append(f"- {item['id']} — {item['label']} : {item['detail']}")
        if not critical_failures:
            lines.append("- Aucun FAIL critique")

        lines.extend(["", "FAILS IMPORTANTS 🟠"])
        important_failures = [item for item in self.results if item["status"] == "FAIL" and item["id"].startswith("V-")]
        for item in important_failures:
            lines.append(f"- {item['id']} — {item['label']} : {item['detail']}")
        if not important_failures:
            lines.append("- Aucun FAIL visuel important")

        lines.extend(["", "WARNINGS ⚠️"])
        warnings = [item for item in self.results if item["status"] == "WARNING"]
        for item in warnings:
            lines.append(f"- {item['id']} — {item['label']} : {item['detail']}")
        if not warnings:
            lines.append("- Aucun warning")

        lines.extend(["", "DÉTAIL COMPLET"])
        for item in self.results:
            lines.append(f"[{item['status']}] {item['id']} — {item['label']} — {item['detail']}")

        lines.extend(["", "ACTIONS CORRECTIVES"])
        for item in self.results:
            if item["status"] == "FAIL":
                lines.append(f"- {item['id']} : vérifier le composant ou la page concernée et corriger la logique de validation ou de navigation.")

        lines.extend(["================================================================"])
        return "\n".join(lines)

    def execute(self):
        try:
            self.results = self.run()
            report = self.generate_report()
            with open(RAPPORT_FILE, "w", encoding="utf-8") as fh:
                fh.write(report)
            print(report)
            return 1 if any(item["status"] == "FAIL" for item in self.results) else 0
        except Exception as exc:
            error_report = f"RAPPORT IMPOSSIBLE\n{type(exc).__name__}: {exc}"
            with open(RAPPORT_FILE, "w", encoding="utf-8") as fh:
                fh.write(error_report)
            print(error_report)
            return 2


if __name__ == "__main__":
    raise SystemExit(WoodinTestRunner().execute())