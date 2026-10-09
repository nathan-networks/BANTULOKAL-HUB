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

  /* ===== Katalog alat dari Laravel API ===== */
  function initCatalogFilter() {
    var grid = document.getElementById("equipmentGrid");
    var filters = Array.prototype.slice.call(
      document.querySelectorAll("#equipmentFilters .filter")
    );
    var emptyState = document.getElementById("catalogEmpty");
    var errorState = document.getElementById("catalogError");
    var retryButton = document.getElementById("retryCatalog");

    if (!grid || filters.length === 0) return;

    var apiUrl = "http://127.0.0.1:8000/api/equipment";
    var activeFilter = "semua";

    function showMessage(message) {
      grid.replaceChildren();
      var paragraph = document.createElement("p");
      paragraph.className = "catalog-message";
      paragraph.textContent = message;
      grid.appendChild(paragraph);
    }


  function createCard(item) {
    var card = document.createElement("article");
    card.className = "equipment-card";

    var link = document.createElement("a");
    link.className = "equipment-card-link";
    link.href = "detail.html?id=" + encodeURIComponent(item.id);
    link.setAttribute("aria-label", "Lihat detail " + item.name);

    var imageBox = document.createElement("div");
    imageBox.className = "equipment-card-image";

    if (item.image_url) {
      var image = document.createElement("img");
      image.src = item.image_url;
      image.alt = item.name;
      image.loading = "lazy";
      imageBox.appendChild(image);
    } else {
      imageBox.classList.add("is-placeholder");

      var placeholder = document.createElement("span");
      placeholder.textContent = "Foto belum tersedia";
      imageBox.appendChild(placeholder);
    }

    var info = document.createElement("div");
    info.className = "equipment-card-info";

    var name = document.createElement("h2");
    name.className = "equipment-card-name";
    name.textContent = item.name;

    var price = document.createElement("p");
    price.className = "equipment-card-price";
    price.textContent =
      "Rp" + Number(item.price_per_day).toLocaleString("id-ID") +
      " / hari";

    var category = document.createElement("p");
    category.className = "equipment-card-category";
    category.textContent = item.category
      ? item.category.name
      : "Lainnya";

    info.append(name, price, category);
    link.append(imageBox, info);
    card.appendChild(link);

    return card;
  }

    async function loadEquipment() {
      if (emptyState) emptyState.hidden = true;
      if (errorState) errorState.hidden = true;

      showMessage("Memuat katalog alat...");

      var url = new URL(apiUrl);

      if (activeFilter !== "semua") {
        url.searchParams.set("category", activeFilter);
      }

      try {
        var response = await fetch(url.toString());

        if (!response.ok) {
          throw new Error("HTTP " + response.status);
        }

        var result = await response.json();
        var items = result.data || [];

        grid.replaceChildren();

        if (items.length === 0) {
          if (emptyState) emptyState.hidden = false;
          return;
        }

        items.forEach(function (item) {
          grid.appendChild(createCard(item));
        });
      } catch (error) {
        console.error("Gagal memuat katalog:", error);
        showMessage("");

        if (errorState) errorState.hidden = false;
      }
    }

    filters.forEach(function (button) {
      button.addEventListener("click", function () {
        activeFilter = button.getAttribute("data-filter");

        filters.forEach(function (filter) {
          filter.setAttribute(
            "aria-pressed",
            filter === button ? "true" : "false"
          );
        });

        loadEquipment();
      });
    });

    if (retryButton) {
      retryButton.addEventListener("click", loadEquipment);
    }

    loadEquipment();
  }

  /* ===== Detail alat dan pengajuan sewa ===== */
  function initEquipmentDetail() {
    var detail = document.getElementById("equipmentDetail");
    if (!detail) return;

    var apiBase = "http://127.0.0.1:8000/api";
    var params = new URLSearchParams(window.location.search);
    var equipmentId = params.get("id");

    var loading = document.getElementById("detailLoading");
    var errorBox = document.getElementById("detailError");
    var errorMessage = document.getElementById("detailErrorMessage");
    var retryButton = document.getElementById("retryDetail");
    var bookingForm = document.getElementById("bookingForm");
    var submitButton = document.getElementById("submitBooking");
    var formStatus = document.getElementById("bookingFormStatus");
    var bookingResult = document.getElementById("bookingResult");
    var bookingResultTitle = document.getElementById("bookingResultTitle");
    var bookingResultContent = document.getElementById("bookingResultContent");

    var termsModal = document.getElementById("termsModal");
    var termsCheckbox = document.getElementById("termsAccepted");
    var confirmTermsButton = document.getElementById("confirmTerms");
    var closeTermsButton = document.getElementById("closeTerms");
    var cancelTermsButton = document.getElementById("cancelTerms");
    var termsValidation = document.getElementById("termsValidation");

    var pendingBookingSubmission = false;
    var previousFocus = null;
    var termsVersion = "rental-terms-v1";

    var equipment = null;
    var pricePerDay = 0;
    var depositAmount = 0;

    var startInput = document.getElementById("rentalStartDate");
    var endInput = document.getElementById("rentalEndDate");

    function rupiah(value) {
      return "Rp" + Number(value || 0).toLocaleString("id-ID");
    }

    function localDateString(date) {
      var year = date.getFullYear();
      var month = String(date.getMonth() + 1).padStart(2, "0");
      var day = String(date.getDate()).padStart(2, "0");
      return year + "-" + month + "-" + day;
    }

    var today = localDateString(new Date());
    startInput.min = today;
    endInput.min = today;

    function updateEstimate() {
      var start = startInput.value;
      var end = endInput.value;
      var duration = document.getElementById("rentalDuration");
      var estimate = document.getElementById("rentalEstimate");

      if (!start || !end) {
        duration.textContent = "Pilih tanggal sewa";
        estimate.textContent = "—";
        return;
      }

      var startDate = new Date(start + "T00:00:00");
      var endDate = new Date(end + "T00:00:00");
      var days = Math.round((endDate - startDate) / 86400000) + 1;

      if (days < 1) {
        duration.textContent = "Tanggal tidak valid";
        estimate.textContent = "—";
        return;
      }

      duration.textContent = days + (days === 1 ? " hari" : " hari");
      estimate.textContent = rupiah(pricePerDay * days);
    }

    startInput.addEventListener("change", function () {
      endInput.min = startInput.value || today;
      if (endInput.value && endInput.value < startInput.value) {
        endInput.value = startInput.value;
      }
      updateEstimate();
    });
    endInput.addEventListener("change", updateEstimate);

    function showError(message) {
      loading.hidden = true;
      detail.hidden = true;
      errorMessage.textContent = message;
      errorBox.hidden = false;
    }

    function displayEquipment(item) {
      equipment = item;
      pricePerDay = Number(item.price_per_day);
      depositAmount = Number(item.deposit_amount);

      document.title = item.name + " | SewaAlatLokal";
      document.getElementById("breadcrumbName").textContent = item.name;
      document.getElementById("detailName").textContent = item.name;
      document.getElementById("detailCategory").textContent =
        item.category ? item.category.name : "Lainnya";
      document.getElementById("detailLocation").textContent =
        item.location || "Lokasi belum dicantumkan";
      document.getElementById("detailPrice").textContent = rupiah(pricePerDay);
      document.getElementById("detailDeposit").textContent = rupiah(depositAmount);
      document.getElementById("rentalDepositSummary").textContent = rupiah(depositAmount);
      document.getElementById("detailCondition").textContent =
        item.condition || "Belum dicantumkan";
      document.getElementById("detailDescription").textContent =
        item.description || "Belum ada deskripsi untuk alat ini.";

      var mainImage = document.getElementById("detailMainImage");
      var thumbnails = document.getElementById("detailThumbnails");
      mainImage.replaceChildren();
      thumbnails.replaceChildren();

      var images = Array.isArray(item.images) ? item.images : [];
      if (images.length === 0) {
        var placeholder = document.createElement("span");
        placeholder.textContent = "Foto belum tersedia";
        mainImage.appendChild(placeholder);
      } else {
        function selectImage(image, button) {
          mainImage.replaceChildren();
          var fullImage = document.createElement("img");
          fullImage.src = image.url;
          fullImage.alt = image.alt_text || item.name;
          mainImage.appendChild(fullImage);

          Array.prototype.forEach.call(
            thumbnails.querySelectorAll("button"),
            function (thumbnail) {
              thumbnail.setAttribute(
                "aria-pressed",
                thumbnail === button ? "true" : "false"
              );
            }
          );
        }

        images.forEach(function (image, index) {
          var button = document.createElement("button");
          button.type = "button";
          button.className = "equipment-detail-thumbnail";
          button.setAttribute("aria-label", "Lihat foto " + (index + 1));
          button.setAttribute("aria-pressed", index === 0 ? "true" : "false");

          var thumbnailImage = document.createElement("img");
          thumbnailImage.src = image.url;
          thumbnailImage.alt = image.alt_text || item.name;
          thumbnailImage.loading = "lazy";
          button.appendChild(thumbnailImage);
          button.addEventListener("click", function () {
            selectImage(image, button);
          });
          thumbnails.appendChild(button);

          if (index === 0) selectImage(image, button);
        });
      }

      loading.hidden = true;
      errorBox.hidden = true;
      detail.hidden = false;
      updateEstimate();
    }

    async function loadDetail() {
      if (!equipmentId || !/^\d+$/.test(equipmentId)) {
        showError("Tautan alat tidak valid. Silakan kembali ke katalog.");
        return;
      }

      loading.hidden = false;
      errorBox.hidden = true;
      detail.hidden = true;

      try {
        var response = await fetch(
          apiBase + "/equipment/" + encodeURIComponent(equipmentId)
        );

        if (response.status === 404) {
          throw new Error("Alat tidak ditemukan atau belum tersedia untuk disewa.");
        }
        if (!response.ok) {
          throw new Error("Server mengembalikan HTTP " + response.status + ".");
        }

        var item = await response.json();
        displayEquipment(item);
      } catch (error) {
        console.error("Gagal memuat detail alat:", error);
        showError(
          error.message === "Failed to fetch"
            ? "Server tidak dapat dihubungi. Pastikan Laravel berjalan dan CORS telah dikonfigurasi."
            : error.message
        );
      }
    }

    retryButton.addEventListener("click", loadDetail);

  function openTermsModal() {
    previousFocus = document.activeElement;
    termsCheckbox.checked = false;
    confirmTermsButton.disabled = true;
    termsValidation.hidden = true;
    termsModal.hidden = false;
    document.body.classList.add("terms-modal-open");
    termsModal.querySelector(".terms-dialog").focus();
    closeTermsButton.focus();
  }

  function closeTermsModal() {
    termsModal.hidden = true;
    document.body.classList.remove("terms-modal-open");
    termsValidation.hidden = true;

    if (previousFocus && typeof previousFocus.focus === "function") {
      previousFocus.focus();
    }
  }

  termsCheckbox.addEventListener("change", function () {
    confirmTermsButton.disabled = !termsCheckbox.checked;
    termsValidation.hidden = true;
  });

  closeTermsButton.addEventListener("click", closeTermsModal);
  cancelTermsButton.addEventListener("click", closeTermsModal);

  termsModal.querySelector("[data-close-terms]").addEventListener(
    "click",
    closeTermsModal
  );

  document.addEventListener("keydown", function (event) {
    if (termsModal.hidden) return;

    if (event.key === "Escape") {
      closeTermsModal();
    }
  });

  bookingForm.addEventListener("submit", function (event) {
    event.preventDefault();

    if (!equipment) return;

    if (!bookingForm.reportValidity()) return;

    if (startInput.value < today || endInput.value < startInput.value) {
      formStatus.textContent = "Periksa kembali tanggal sewa.";
      return;
    }

    pendingBookingSubmission = true;
    openTermsModal();
  });

  confirmTermsButton.addEventListener("click", async function () {
    if (!pendingBookingSubmission || !equipment) return;

    if (!termsCheckbox.checked) {
      termsValidation.hidden = false;
      return;
    }

    pendingBookingSubmission = false;
    closeTermsModal();

    bookingResult.hidden = true;
    formStatus.textContent = "";

    submitButton.disabled = true;
    confirmTermsButton.disabled = true;
    submitButton.textContent = "Mengirim pengajuan...";

    var payload = {
      equipment_id: Number(equipment.id),
      customer_name: document.getElementById("customerName").value.trim(),
      customer_email: document.getElementById("customerEmail").value.trim(),
      customer_whatsapp: document.getElementById("customerWhatsapp").value.trim(),
      rental_start_date: startInput.value,
      rental_end_date: endInput.value,
      customer_notes:
        document.getElementById("customerNotes").value.trim() || null,
      terms_accepted: true,
      terms_version: termsVersion
    };

    try {
      var response = await fetch(apiBase + "/bookings", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        body: JSON.stringify(payload)
      });

      var result = await response.json();

      if (!response.ok) {
        if (response.status === 422 && result.errors) {
          var messages = Object.values(result.errors).flat();
          throw new Error(messages.join(" "));
        }

        throw new Error(
          result.message || "Pengajuan belum berhasil dikirim."
        );
      }

      bookingResultTitle.textContent = "Pengajuan sewa berhasil dikirim";
      bookingResultContent.replaceChildren();

      var reference = document.createElement("p");
      reference.textContent = "Nomor pengajuan: " + result.booking.id;

      var status = document.createElement("p");
      status.textContent = "Status: Menunggu pemeriksaan admin (pending).";

      var total = document.createElement("p");
      total.textContent =
        "Estimasi biaya sewa: " + rupiah(result.booking.total_amount);

      var deposit = document.createElement("p");
      deposit.textContent =
        "Deposit terpisah: " + rupiah(result.booking.deposit_amount);

      var note = document.createElement("p");
      note.textContent =
        "Admin akan memeriksa ketersediaan dan menghubungi kamu. " +
        "Pengajuan ini belum merupakan konfirmasi sewa.";

      bookingResultContent.append(reference, status, total, deposit, note);
      bookingResult.hidden = false;
      bookingResult.focus();

      bookingForm.reset();
      startInput.min = today;
      endInput.min = today;
      updateEstimate();
    } catch (error) {
      console.error("Gagal mengirim pengajuan sewa:", error);
      formStatus.textContent =
        error.message || "Pengajuan gagal dikirim. Silakan coba lagi.";
    } finally {
      submitButton.disabled = false;
      submitButton.textContent = "Kirim pengajuan sewa";
    }
  });


    loadDetail();
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

  /* ===== Form pendaftaran freelancer ===== */
  function initFreelancerApplicationForm() {
    var form = document.getElementById("freelancerApplicationForm");
    if (!form) return;

    var status = document.getElementById("freelancerApplicationStatus");
    var submitButton = document.getElementById("freelancerSubmit");

    var fields = [
      { id: "freelancerName", error: "freelancerNameError" },
      { id: "freelancerWhatsapp", error: "freelancerWhatsappError" },
      { id: "freelancerEmail", error: "freelancerEmailError" },
      { id: "freelancerSkills", error: "freelancerSkillsError" }
    ];

    function showStatus(message, isError) {
      status.textContent = message;
      status.hidden = false;
      status.setAttribute("aria-live", "polite");
      status.style.color = isError ? "#b42318" : "#18794e";
    }

    fields.forEach(function (field) {
      var input = document.getElementById(field.id);
      var error = document.getElementById(field.error);
      if (!input || !error) return;

      input.addEventListener("input", function () {
        error.hidden = true;
        input.removeAttribute("aria-invalid");
      });
    });

    form.addEventListener("submit", async function (event) {
      event.preventDefault();
      status.hidden = true;

      var name = document.getElementById("freelancerName").value.trim();
      var whatsapp = document.getElementById("freelancerWhatsapp").value.trim();
      var email = document.getElementById("freelancerEmail").value.trim();
      var skills = document.getElementById("freelancerSkills").value.trim();
      var experience = document.getElementById("freelancerExperience").value.trim();

      var invalid = false;

      fields.forEach(function (field) {
        var input = document.getElementById(field.id);
        var error = document.getElementById(field.error);
        var value = input.value.trim();
        var hasError = false;

        if (field.id === "freelancerName" || field.id === "freelancerSkills") {
          hasError = value === "";
        }

        if (field.id === "freelancerWhatsapp") {
          hasError = !/^[0-9+()\s-]{8,20}$/.test(value);
        }

        if (field.id === "freelancerEmail" && value !== "") {
          hasError = !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        }

        error.hidden = !hasError;
        input.toggleAttribute("aria-invalid", hasError);

        if (hasError) invalid = true;
      });

      if (invalid) {
        showStatus("Periksa kembali data yang wajib diisi.", true);
        var firstInvalid = fields
          .map(function (field) {
            return document.getElementById(field.id);
          })
          .find(function (input) {
            return input.getAttribute("aria-invalid") === "true";
          });

        if (firstInvalid) firstInvalid.focus();
        return;
      }

      submitButton.disabled = true;
      submitButton.textContent = "Mengirim...";

      try {
        var response = await fetch(
          "http://127.0.0.1:8000/api/freelancer-applications",
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "Accept": "application/json"
            },
            body: JSON.stringify({
              name: name,
              whatsapp: whatsapp,
              email: email || null,
              skills: skills,
              experience: experience || null
            })
          }
        );

        var data = await response.json();

        if (!response.ok) {
          if (response.status === 422 && data.errors) {
            var errorMessages = [];

            Object.keys(data.errors).forEach(function (key) {
              errorMessages.push(data.errors[key][0]);
            });

            showStatus(errorMessages.join(" "), true);
          } else {
            showStatus(
              data.message || "Pendaftaran gagal dikirim. Silakan coba lagi.",
              true
            );
          }
          return;
        }

        showStatus(
          data.message ||
            "Pendaftaran berhasil dikirim. Tim BANTULOKAL HUB akan meninjau pendaftaranmu.",
          false
        );

        form.reset();
      } catch (error) {
        showStatus(
          "Tidak dapat terhubung ke server. Pastikan Laravel berjalan, lalu coba lagi.",
          true
        );
      } finally {
        submitButton.disabled = false;
        submitButton.textContent = "Ajukan pendaftaran";
      }
    });
  }


    /* ===== Form permintaan jasa: worker slider ===== */

    function initServiceRequestForm() {
      var form = document.getElementById("serviceRequestForm");
      if (!form) return;

      var slider = document.getElementById("requestWorkers");
      var workerValue = document.getElementById("requestWorkersValue");
      var status = document.getElementById("serviceRequestStatus");
      var submitButton = document.getElementById("requestSubmit");

      function updateWorkerValue() {
        if (slider && workerValue) {
          workerValue.textContent = slider.value + " orang";
        }
      }

      if (slider) {
        slider.addEventListener("input", updateWorkerValue);
        updateWorkerValue();
      }

      form.addEventListener("submit", async function (event) {
        event.preventDefault();

        if (!form.reportValidity()) return;

        var originalButtonText = submitButton.textContent;

        submitButton.disabled = true;
        submitButton.textContent = "Mengirim...";

        status.hidden = false;
        status.textContent = "Mengirim permintaan...";

        var payload = {
          customer_name: document.getElementById("requestCustomerName").value.trim(),
          customer_email: document.getElementById("requestEmail").value.trim(),
          customer_whatsapp: document.getElementById("requestWhatsapp").value.trim(),
          category: document.getElementById("requestCategory").value,
          description: document.getElementById("requestDescription").value.trim(),
          location: document.getElementById("requestLocation").value.trim(),
          worker_count: Number(slider.value),
          preferred_date: document.getElementById("requestDate").value || null,
          terms_accepted: document.getElementById("requestTermsAccepted").checked
        };

        try {
          var response = await fetch(
            "http://127.0.0.1:8000/api/service-requests",
            {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
              },
              body: JSON.stringify(payload)
            }
          );

          var result = await response.json();

          if (!response.ok) {
            var errorMessage = result.errors
              ? Object.values(result.errors).flat().join(" ")
              : result.message || "Permintaan gagal dikirim.";

            throw new Error(errorMessage);
          }

          status.textContent =
            "Permintaan #" + result.data.id +
            " berhasil dikirim dan sedang menunggu peninjauan. " +
            "Biaya belum ditetapkan dan permintaan belum dikonfirmasi.";

          form.reset();
          updateWorkerValue();
        } catch (error) {
          status.textContent =
            "Gagal mengirim permintaan: " + error.message;
        } finally {
          submitButton.disabled = false;
          submitButton.textContent = originalButtonText;
        }
      });
    }


  initNav();
  initCatalogFilter();
  initDaftarForm();
  initEquipmentDetail();
  initFreelancerApplicationForm();
  initServiceRequestForm();

})();
