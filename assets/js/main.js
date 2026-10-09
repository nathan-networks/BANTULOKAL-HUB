/* main.js: satu script klasik (bukan ES module): import module diblokir
   browser saat file dibuka via file:// (CORS origin null). Pola tetap sama:
   init per fitur berdasarkan keberadaan elemen, aman untuk semua halaman. */
(function () {
  "use strict";

  /* ===== Nav mobile ===== */
  function initNav() {
    var navToggle = document.getElementById("navToggle");
    var navMenu = document.getElementById("navMenu");
    if (!navToggle || !navMenu) return;

    var closeMenu = function () {
      navMenu.classList.remove("is-open");
      navToggle.setAttribute("aria-expanded", "false");
    };

    navToggle.addEventListener("click", function () {
      var open = navMenu.classList.toggle("is-open");
      navToggle.setAttribute("aria-expanded", open ? "true" : "false");
    });

    navMenu.addEventListener("click", function (e) {
      if (e.target.closest("a")) closeMenu();
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && navMenu.classList.contains("is-open")) {
        closeMenu();
        navToggle.focus();
      }
    });

    document.addEventListener("click", function (e) {
      if (!navMenu.classList.contains("is-open")) return;
      if (!e.target.closest(".nav")) closeMenu();
    });
  }

  /* ===== Filter katalog ===== */
  function initCatalogFilter() {
    var filters = Array.prototype.slice.call(document.querySelectorAll(".filter"));
    var cards = Array.prototype.slice.call(document.querySelectorAll(".catcard"));
    if (filters.length === 0 || cards.length === 0) return;

    var emptyState = document.getElementById("catalogEmpty");

    filters.forEach(function (btn) {
      btn.addEventListener("click", function () {
        var value = btn.getAttribute("data-filter");
        filters.forEach(function (b) {
          b.setAttribute("aria-pressed", b === btn ? "true" : "false");
        });
        var visible = 0;
        cards.forEach(function (card) {
          var show = value === "semua" || card.getAttribute("data-cat") === value;
          card.classList.toggle("is-hidden", !show);
          if (show) visible += 1;
        });
        if (emptyState) emptyState.hidden = visible > 0;
      });
    });
  }

  /* ===== Form pendaftaran alat ===== */
  var FIELDS = [
    { id: "fNama", err: "errNama", label: "Nama alat" },
    { id: "fKategori", err: "errKategori", label: "Kategori" },
    { id: "fPemilik", err: "errPemilik", label: "Nama pemilik" },
    { id: "fKontak", err: "errKontak", label: "Nomor WhatsApp" }
  ];

  function setError(input, errEl, hasError) {
    errEl.hidden = !hasError;
    if (hasError) input.setAttribute("aria-invalid", "true");
    else input.removeAttribute("aria-invalid");
  }

  function initDaftarForm() {
    var form = document.getElementById("formDaftar");
    if (!form) return;

    var result = document.getElementById("formResult");
    var resultList = document.getElementById("resultList");

    form.addEventListener("submit", function (e) {
      e.preventDefault();

      var firstInvalid = null;

      FIELDS.forEach(function (f) {
        var input = document.getElementById(f.id);
        var errEl = document.getElementById(f.err);
        var value = input.value.trim();
        var invalid = value === "";

        if (f.id === "fKontak" && value !== "") {
          invalid = !/^[0-9+()\s-]{8,20}$/.test(value);
        }

        setError(input, errEl, invalid);
        if (invalid && !firstInvalid) firstInvalid = input;
      });

      if (firstInvalid) {
        firstInvalid.focus();
        if (result) result.hidden = true;
        return;
      }

      if (result && resultList) {
        resultList.innerHTML = "";
        FIELDS.forEach(function (f) {
          var input = document.getElementById(f.id);
          var dt = document.createElement("dt");
          var dd = document.createElement("dd");
          dt.textContent = input.getAttribute("data-label") || f.label;
          dd.textContent = input.value.trim();
          resultList.appendChild(dt);
          resultList.appendChild(dd);
        });
        result.hidden = false;
        result.focus();
      } else {
        /* Halaman tanpa panel hasil (mis. katalog): feedback via status inline. */
        var status = document.getElementById("formStatus");
        if (status) {
          status.hidden = false;
          status.focus();
        }
        form.reset();
      }
    });

    FIELDS.forEach(function (f) {
      var input = document.getElementById(f.id);
      if (!input) return;
      input.addEventListener("input", function () {
        setError(input, document.getElementById(f.err), false);
      });
    });
  }

  initNav();
  initCatalogFilter();
  initDaftarForm();
})();
