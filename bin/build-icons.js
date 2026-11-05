#!/usr/bin/env node
/**
 * Génère public/assets/js/icons.js : un jeu RÉDUIT d'icônes Lucide.
 *
 * Pourquoi : la bibliothèque complète (lucide.min.js, 480 Ko, 2 300 icônes)
 * était chargée sur chaque page pour une centaine d'icônes ; son exécution
 * retardait l'affichage du hero sur mobile. Le fichier généré expose la même
 * API (`lucide.createIcons()`), produit le même balisage SVG et, si une page
 * contient une icône absente du jeu (choisie plus tard dans l'admin), charge
 * la bibliothèque complète en secours : aucune icône ne manque jamais.
 *
 * Usage (Node 18+, accès réseau) :
 *   node bin/build-icons.js [https://digitaliumgroup.com]
 * Les noms sont relevés dans les vues frontend (data-lucide="…") et, si une
 * adresse est donnée, dans toutes les pages de son sitemap (icônes saisies
 * en admin). À relancer après l'ajout d'icônes, puis committer le fichier.
 */
'use strict';
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const VERSION = '1.69.0';
const RACINE = path.resolve(__dirname, '..');
const SORTIE = path.join(RACINE, 'public/assets/js/icons.js');
const TOUJOURS = ['menu', 'x', 'check', 'chevron-down', 'chevron-left', 'chevron-right', 'arrow-right'];

function vues(dir, noms) {
    for (const f of fs.readdirSync(dir, { withFileTypes: true })) {
        const p = path.join(dir, f.name);
        if (f.isDirectory()) { vues(p, noms); continue; }
        if (!f.name.endsWith('.php')) { continue; }
        for (const m of fs.readFileSync(p, 'utf8').matchAll(/data-lucide=["']([a-z0-9-]+)["']/g)) { noms.add(m[1]); }
    }
}

(async () => {
    const noms = new Set(TOUJOURS);
    vues(path.join(RACINE, 'app/Views/frontend'), noms);

    const site = process.argv[2];
    if (site) {
        const sm = await (await fetch(site.replace(/\/$/, '') + '/sitemap.xml')).text();
        for (const [, u] of sm.matchAll(/<loc>([^<]+)<\/loc>/g)) {
            const h = await (await fetch(u)).text();
            for (const m of h.matchAll(/data-lucide=["']([a-z0-9-]+)["']/g)) { noms.add(m[1]); }
        }
    }

    const src = await (await fetch(`https://unpkg.com/lucide@${VERSION}/dist/umd/lucide.js`)).text();
    const ctx = {}; ctx.self = ctx; ctx.window = ctx; vm.createContext(ctx); vm.runInContext(src, ctx);
    const tous = ctx.lucide.icons;
    const pascal = n => n.replace(/(^|[-_ ])([a-z0-9])/g, (_, s, c) => c.toUpperCase());

    const jeu = {}; const inconnus = [];
    for (const n of [...noms].sort()) {
        const noeud = tous[pascal(n)];
        if (noeud) { jeu[n] = noeud; } else { inconnus.push(n); }
    }

    const corps = `/* Jeu réduit d'icônes Lucide ${VERSION} (ISC) — ${Object.keys(jeu).length} icônes.
   FICHIER GÉNÉRÉ par bin/build-icons.js : ne pas modifier à la main.
   Même API et même balisage que la bibliothèque complète ; une icône absente
   du jeu déclenche le chargement de la bibliothèque complète en secours. */
(function () {
    var I = ${JSON.stringify(jeu)};
    /* Noms absents de Lucide lui-même (icônes de marque retirées) : la
       bibliothèque complète ne les rendrait pas non plus, inutile de la charger. */
    var ABSENTS = ${JSON.stringify(inconnus)};
    var NS = 'http://www.w3.org/2000/svg';
    var BASE = { xmlns: NS, width: 24, height: 24, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor',
                 'stroke-width': 2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' };
    var COMPLETE = 'https://unpkg.com/lucide@${VERSION}/dist/umd/lucide.min.js';
    var secours = false;

    function noeud(tag, attrs, enfants) {
        var e = document.createElementNS(NS, tag);
        for (var k in attrs) { e.setAttribute(k, String(attrs[k])); }
        (enfants || []).forEach(function (c) { e.appendChild(noeud(c[0], c[1], c[2])); });
        return e;
    }

    function remplacer(el) {
        var nom = el.getAttribute('data-lucide');
        if (!I[nom]) { return false; }
        var attrs = {}, k, a11y = false;
        for (k in BASE) { attrs[k] = BASE[k]; }
        attrs['data-lucide'] = nom;
        Array.prototype.forEach.call(el.attributes, function (at) {
            if (at.name.indexOf('aria-') === 0 || at.name === 'role' || at.name === 'title') { a11y = true; }
            attrs[at.name] = at.value;
        });
        if (!a11y) { attrs['aria-hidden'] = 'true'; }
        var classes = ['lucide', 'lucide-' + nom];
        (el.getAttribute('class') || '').split(/\\s+/).forEach(function (c) {
            if (c && c.indexOf('lucide-') !== 0 && classes.indexOf(c) < 0) { classes.push(c); }
        });
        attrs['class'] = classes.join(' ');
        var svg = noeud('svg', attrs, I[nom]);
        if (el.parentNode) { el.parentNode.replaceChild(svg, el); }
        return true;
    }

    function chargerComplete() {
        if (secours) { return; }
        secours = true;
        var s = document.createElement('script');
        s.src = COMPLETE;
        s.onload = function () { if (window.lucide && window.lucide.icons) { window.lucide.createIcons(); } };
        document.head.appendChild(s);
    }

    window.lucide = {
        createIcons: function () {
            var manque = false;
            Array.prototype.forEach.call(document.querySelectorAll('[data-lucide]'), function (el) {
                if (!remplacer(el) && ABSENTS.indexOf(el.getAttribute('data-lucide')) < 0) { manque = true; }
            });
            if (manque) { chargerComplete(); }
        }
    };
})();
`;
    fs.mkdirSync(path.dirname(SORTIE), { recursive: true });
    fs.writeFileSync(SORTIE, corps);
    console.log(`${Object.keys(jeu).length} icônes -> ${path.relative(RACINE, SORTIE)} (${Math.round(corps.length / 1024)} Ko)`);
    if (inconnus.length) { console.log('Noms inconnus de Lucide (ignorés) :', inconnus.join(', ')); }
})();
