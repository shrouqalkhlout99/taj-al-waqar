(function () {
  const app = document.getElementById("app");
  let selectedStudentId = Store.data.students[0] ? Store.data.students[0].id : null;
  let modal = null;
  let toastTimer = null;

  const NAV = [
    ["#/", "لوحة اليوم"],
    ["#/students", "الطلاب"],
    ["#/appointments", "المواعيد"],
    ["#/lessons", "الحصص"],
    ["#/notes", "الملاحظات"],
    ["#/book", "الكتاب والشرح"],
    ["#/assistant", "المساعد الذكي"],
    ["#/reports", "التقارير"],
    ["#/settings", "الإعدادات"]
  ];

  function route() {
    const hash = location.hash || "#/";
    const [path, query] = hash.split("?");
    const params = new URLSearchParams(query || "");
    return { path, params };
  }

  function arabicDate(d = new Date()) {
    return d.toLocaleDateString("ar-EG", {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric"
    });
  }

  function greeting() {
    const h = new Date().getHours();
    if (h < 12) return "صباح الخير";
    if (h < 18) return "مساء الخير";
    return "مساء الخير";
  }

  function initials(name) {
    return (name || "ط")
      .split(" ")
      .slice(0, 2)
      .map((p) => p[0])
      .join("");
  }

  function statusLabel(s) {
    return { upcoming: "قادمة", done: "منتهية", progress: "جارية", cancelled: "ملغاة" }[s] || s;
  }

  function toast(msg) {
    let el = document.querySelector(".toast");
    if (!el) {
      el = document.createElement("div");
      el.className = "toast";
      document.body.appendChild(el);
    }
    el.textContent = msg;
    el.classList.remove("hidden");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.add("hidden"), 2200);
  }

  function layout(content, extraHeader = "") {
    const { path } = route();
    const today = Store.todayAppointments();
    const done = today.filter((a) => a.status === "done").length;
    const remaining = today.filter((a) => a.status !== "done" && a.status !== "cancelled").length;
    const name = Store.data.settings.teacherName || "معلمة قرآن";

    return `
      <aside class="sidebar">
        <div class="brand">
          <div class="brand-icon">📖</div>
          <h1>نظام معلمة القرآن</h1>
        </div>
        <nav class="nav">
          ${NAV.map(
            ([href, label]) =>
              `<a href="${href}" class="${path === href ? "active" : ""}">${label}</a>`
          ).join("")}
        </nav>
        <div class="today-card">
          <h3>حصص اليوم</h3>
          <div class="stats">
            <div><b>${today.length}</b><span>الإجمالي</span></div>
            <div><b>${done}</b><span>منتهية</span></div>
            <div><b>${remaining}</b><span>متبقية</span></div>
          </div>
        </div>
        <div class="ayah-quote">وَرَتِّلِ الْقُرْآنَ تَرْتِيلًا</div>
      </aside>
      <section class="main">
        <nav class="mobile-nav">
          ${NAV.map(
            ([href, label]) =>
              `<a href="${href}" class="${path === href ? "active" : ""}">${label}</a>`
          ).join("")}
        </nav>
        <header class="header">
          <div>
            <h2>${greeting()}، ${name.split(" ")[0]}</h2>
            <p>${arabicDate()}</p>
          </div>
          <div class="user-chip">
            <div class="avatar" style="background:#6b4bd6">${initials(name)}</div>
            <strong>${name}</strong>
          </div>
        </header>
        ${extraHeader}
        ${content}
      </section>
      <div id="modal-root"></div>
    `;
  }

  function studentCard(s, compact) {
    if (!s) return `<div class="card empty">اختاري طالباً من الجدول</div>`;
    return `
      <div class="card">
        <div class="profile-head">
          <div class="s-avatar" style="background:${s.color}">${initials(s.name)}</div>
          <h3>${s.name}</h3>
          <div class="level">${s.level} · ${s.style}</div>
        </div>
        <div class="meet-box">
          <input id="meet-link" readonly value="${s.meetLink || ""}" />
          <button class="btn btn-ghost" data-copy="${s.meetLink || ""}">نسخ</button>
          ${s.meetLink ? `<a class="btn btn-primary" href="${s.meetLink}" target="_blank">دخول</a>` : ""}
        </div>
        <div class="progress-grid">
          <div><small>آخر موضع</small><b>${s.currentSurah} ${s.lastAyah || ""}</b></div>
          <div><small>حفظ جديد</small><b>${Store.rangeText(s.newMem)}</b></div>
          <div><small>التسميع</small><b>${Store.rangeText(s.recitation)}</b></div>
          <div><small>المراجعة</small><b>${Store.rangeText(s.review)}</b></div>
        </div>
        <p style="font-weight:800;margin:0 0 8px">آخر ملاحظة</p>
        <div class="note-box">${s.lastNote || "لا توجد ملاحظات بعد."}<br><small>${s.lastNoteDate || ""}</small></div>
        ${compact ? "" : `<p style="margin:14px 0 0"><a class="btn btn-ghost" href="#/students?id=${s.id}">سجل الحصص</a></p>`}
      </div>
    `;
  }

  function dashboard() {
    const rows = Store.todayAppointments();
    const selected = Store.student(selectedStudentId) || (rows[0] && Store.student(rows[0].studentId));
    if (selected) selectedStudentId = selected.id;
    const table = rows.length
      ? `<table>
          <thead><tr><th>الوقت</th><th>الطالب</th><th>المطلوب</th><th>الحالة</th><th></th></tr></thead>
          <tbody>
            ${rows
              .map((a) => {
                const s = Store.student(a.studentId);
                if (!s) return "";
                return `<tr data-select="${s.id}">
                  <td>${a.time}</td>
                  <td><div class="student-cell"><div class="s-avatar" style="background:${s.color}">${initials(s.name)}</div>${s.name}</div></td>
                  <td>${Store.requirement(s)}</td>
                  <td><span class="badge ${a.status}">${statusLabel(a.status)}</span></td>
                  <td class="row-actions">
                    ${
                      a.status === "upcoming" || a.status === "progress"
                        ? `<button class="btn btn-primary" data-start="${a.id}">ابدئي الحصة</button>`
                        : `<button class="btn btn-ghost" data-history="${s.id}">السجل</button>`
                    }
                  </td>
                </tr>`;
              })
              .join("")}
          </tbody>
        </table>`
      : `<div class="empty">لا توجد حصص اليوم. أضيفي موعداً من صفحة المواعيد.</div>`;

    app.innerHTML = layout(
      `<div class="content-grid">
        <div class="card"><h3>جدول اليوم</h3>${table}</div>
        ${studentCard(selected)}
      </div>`
    );
  }

  function studentsPage() {
    const { params } = route();
    const focus = params.get("id");
    const q = (params.get("q") || "").trim();
    let list = Store.data.students;
    if (q) list = list.filter((s) => s.name.includes(q) || (s.phone || "").includes(q));
    const focused = Store.student(focus);
    app.innerHTML = layout(`
      <div class="toolbar">
        <input class="search" id="student-search" placeholder="بحث عن طالب..." value="${q}" />
        <button class="btn btn-primary" id="add-student">إضافة طالب</button>
      </div>
      <div class="content-grid">
        <div class="list">
          ${
            list
              .map(
                (s) => `<div class="list-item">
                  <div class="student-cell">
                    <div class="s-avatar" style="background:${s.color}">${initials(s.name)}</div>
                    <div>
                      <b>${s.name}</b>
                      <div style="color:var(--muted);font-size:13px">${s.level} · ${Store.requirement(s)}</div>
                    </div>
                  </div>
                  <div class="row-actions">
                    <button class="btn btn-ghost" data-edit-student="${s.id}">تعديل</button>
                    <button class="btn btn-primary" data-open-student="${s.id}">الملف</button>
                  </div>
                </div>`
              )
              .join("") || `<div class="empty">لا يوجد طلاب بعد.</div>`
          }
        </div>
        <div>
          ${studentCard(focused || list[0], true)}
          ${lessonHistory((focused || list[0] || {}).id)}
        </div>
      </div>
    `);
  }

  function lessonHistory(studentId) {
    const items = Store.data.lessons.filter((l) => l.studentId === studentId);
    if (!items.length) return `<div class="card" style="margin-top:12px"><h3>سجل الحصص</h3><div class="empty">لا توجد حصص محفوظة.</div></div>`;
    return `<div class="card" style="margin-top:12px"><h3>سجل الحصص</h3>${items
      .map(
        (l) => `<div style="border-top:1px solid var(--line);padding:10px 0">
          <b>${l.date || ""} ${l.time || ""}</b>
          <div>${l.aiSummary || l.notes || ""}</div>
        </div>`
      )
      .join("")}</div>`;
  }

  function appointmentsPage() {
    const items = [...Store.data.appointments].sort((a, b) => (a.date + a.time).localeCompare(b.date + b.time));
    app.innerHTML = layout(`
      <div class="toolbar">
        <button class="btn btn-primary" id="add-apt">إضافة موعد</button>
      </div>
      <div class="card">
        <table>
          <thead><tr><th>التاريخ</th><th>الوقت</th><th>الطالب</th><th>الحالة</th><th></th></tr></thead>
          <tbody>
            ${items
              .map((a) => {
                const s = Store.student(a.studentId);
                return `<tr>
                  <td>${a.date}</td><td>${a.time}</td>
                  <td>${s ? s.name : "طالب محذوف"}</td>
                  <td><span class="badge ${a.status}">${statusLabel(a.status)}</span></td>
                  <td class="row-actions">
                    <button class="btn btn-ghost" data-edit-apt="${a.id}">تعديل</button>
                    <button class="btn btn-danger" data-cancel-apt="${a.id}">إلغاء</button>
                  </td>
                </tr>`;
              })
              .join("")}
          </tbody>
        </table>
      </div>
    `);
  }

  function lessonsPage() {
    const items = Store.data.lessons;
    app.innerHTML = layout(`
      <div class="card">
        <h3>الحصص السابقة</h3>
        ${
          items.length
            ? items
                .map((l) => {
                  const s = Store.student(l.studentId);
                  return `<div class="list-item" style="margin-bottom:10px">
                    <div>
                      <b>${s ? s.name : ""}</b>
                      <div style="color:var(--muted)">${l.date} ${l.time || ""}</div>
                      <div>${l.aiSummary || l.notes || ""}</div>
                    </div>
                    <span class="badge ${l.recitationGrade || "good"}">${statusLabel("done")}</span>
                  </div>`;
                })
                .join("")
            : `<div class="empty">بعد إنهاء أول حصة ستظهر هنا تلقائياً.</div>`
        }
      </div>
    `);
  }

  function notesPage() {
    app.innerHTML = layout(`
      <div class="list">
        ${Store.data.students
          .map(
            (s) => `<div class="card">
              <h3>${s.name}</h3>
              <div class="note-box">${s.lastNote || "لا توجد ملاحظات."}<br><small>${s.lastNoteDate || ""}</small></div>
            </div>`
          )
          .join("")}
      </div>
    `);
  }

  function bookPage() {
    const s = Store.student(selectedStudentId) || Store.data.students[0];
    app.innerHTML = layout(`
      <div class="card">
        <h3>الكتاب والشرح</h3>
        <p>اختاري الطالب والآية، ثم اضغطي «جهّزي الشرح» ليظهر نص الآية وشرح مناسب لمستواه.</p>
        ${explainForm(s, false)}
        <div id="explain-result"></div>
      </div>
    `);
  }

  function assistantPage() {
    const s = Store.student(selectedStudentId) || Store.data.students[0];
    app.innerHTML = layout(`
      <div class="card">
        <h3>المساعد الذكي — شرح الآية</h3>
        ${explainForm(s, true)}
        <div id="explain-result"></div>
      </div>
    `);
  }

  function explainForm(s) {
    const students = Store.data.students
      .map((st) => `<option value="${st.id}" ${s && st.id === s.id ? "selected" : ""}>${st.name}</option>`)
      .join("");
    const surah = (s && s.currentSurah) || "البقرة";
    const ayah = (s && (s.newMem.to || s.lastAyah)) || 1;
    return `
      <div class="form-grid">
        <div class="field"><label>الطالب</label><select id="ex-student">${students}</select></div>
        <div class="field"><label>السورة</label><select id="ex-surah">${surahOptions(surah)}</select></div>
        <div class="field"><label>الآية</label><input id="ex-ayah" type="number" min="1" value="${ayah}" /></div>
        <div class="field"><label>&nbsp;</label><button class="btn btn-primary" id="run-explain">جهّزي الشرح</button></div>
      </div>
    `;
  }

  function reportsPage() {
    const total = Store.data.lessons.length;
    const students = Store.data.students.length;
    app.innerHTML = layout(`
      <div class="progress-grid">
        <div class="card"><small>عدد الطلاب</small><b style="font-size:28px">${students}</b></div>
        <div class="card"><small>الحصص المسجّلة</small><b style="font-size:28px">${total}</b></div>
        <div class="card"><small>حصص اليوم</small><b style="font-size:28px">${Store.todayAppointments().length}</b></div>
        <div class="card"><small>المنتهية اليوم</small><b style="font-size:28px">${Store.todayAppointments().filter((a) => a.status === "done").length}</b></div>
      </div>
    `);
  }

  function settingsPage() {
    const s = Store.data.settings;
    app.innerHTML = layout(`
      <div class="card" style="max-width:640px">
        <h3>الإعدادات</h3>
        <div class="field"><label>اسم المعلمة</label><input id="set-name" value="${s.teacherName || ""}" /></div>
        <div class="field" style="margin-top:12px"><label>مفتاح OpenAI (اختياري للمساعد الذكي)</label>
          <input id="set-key" type="password" value="${s.openaiKey || ""}" placeholder="sk-..." />
        </div>
        <div class="field" style="margin-top:12px"><label>النموذج</label>
          <input id="set-model" value="${s.openaiModel || "gpt-4o-mini"}" />
        </div>
        <p style="color:var(--muted)">بدون المفتاح يشتغل المساعد بأسلوب جاهز مناسب للمستوى. المفتاح يُحفظ على هذا الجهاز فقط.</p>
        <button class="btn btn-primary" id="save-settings">حفظ</button>
      </div>
    `);
  }

  function rangeFields(prefix, data) {
    data = data || {};
    return `
      <div class="form-grid">
        <div class="field"><label>السورة</label><select name="${prefix}-surah">${surahOptions(data.surah)}</select></div>
        <div class="field"><label>من آية</label><input name="${prefix}-from" type="number" min="1" value="${data.from || ""}" /></div>
        <div class="field"><label>إلى آية</label><input name="${prefix}-to" type="number" min="1" value="${data.to || ""}" /></div>
      </div>
    `;
  }

  function gradeFields(name, value) {
    return `<div class="grades">${GRADES.map(
      (g) =>
        `<label><input type="radio" name="${name}" value="${g.id}" ${value === g.id ? "checked" : ""} /><span>${g.label}</span></label>`
    ).join("")}</div>`;
  }

  function showModal(html) {
    const root = document.getElementById("modal-root");
    root.innerHTML = `<div class="modal-backdrop">${html}</div>`;
    modal = root;
  }

  function closeModal() {
    const root = document.getElementById("modal-root");
    if (root) root.innerHTML = "";
  }

  function studentForm(existing) {
    const s = existing || {
      name: "",
      phone: "",
      level: "مبتدئ",
      style: "شرح مبسط",
      meetLink: "",
      currentSurah: "الفاتحة",
      lastAyah: 1,
      newMem: {},
      recitation: {},
      review: {}
    };
    showModal(`
      <form class="modal" id="student-form">
        <h3>${existing ? "تعديل ملف الطالب" : "ملف طالب جديد"}</h3>
        <input type="hidden" name="studentRecordId" value="${s.id || ""}" />
        <div class="form-grid">
          <div class="field"><label>الاسم</label><input name="name" required value="${s.name}" /></div>
          <div class="field"><label>الجوال</label><input name="phone" value="${s.phone || ""}" /></div>
          <div class="field"><label>المستوى</label>
            <select name="level">${LEVELS.map((l) => `<option ${l === s.level ? "selected" : ""}>${l}</option>`).join("")}</select>
          </div>
          <div class="field"><label>طريقة الشرح المناسبة</label>
            <select name="style">${STYLES.map((l) => `<option ${l === s.style ? "selected" : ""}>${l}</option>`).join("")}</select>
          </div>
          <div class="field full"><label>رابط Google Meet</label><input name="meetLink" value="${s.meetLink || ""}" /></div>
          <div class="field"><label>آخر سورة وصل لها</label><select name="currentSurah">${surahOptions(s.currentSurah)}</select></div>
          <div class="field"><label>آخر آية</label><input name="lastAyah" type="number" value="${s.lastAyah || ""}" /></div>
        </div>
        <p><b>الحفظ الجديد</b></p>${rangeFields("newMem", s.newMem)}
        <p><b>التسميع</b></p>${rangeFields("recitation", s.recitation)}
        <p><b>المراجعة</b></p>${rangeFields("review", s.review)}
        <div class="field full" style="margin-top:10px"><label>ملاحظة</label><textarea name="lastNote">${s.lastNote || ""}</textarea></div>
        <div class="modal-actions">
          <button type="button" class="btn btn-outline" data-close>إلغاء</button>
          <button class="btn btn-primary" type="submit">حفظ الملف</button>
        </div>
      </form>
    `);
  }

  function aptForm(existing) {
    const a = existing || { date: Store.todayISO(), time: "16:00", studentId: Store.data.students[0] && Store.data.students[0].id };
    showModal(`
      <form class="modal" id="apt-form">
        <h3>${existing ? "تعديل الموعد" : "موعد جديد"}</h3>
        <input type="hidden" name="aptId" value="${a.id || ""}" />
        <div class="form-grid">
          <div class="field"><label>الطالب</label>
            <select name="studentId">${Store.data.students.map((s) => `<option value="${s.id}" ${s.id === a.studentId ? "selected" : ""}>${s.name}</option>`).join("")}</select>
          </div>
          <div class="field"><label>التاريخ</label><input type="date" name="date" value="${a.date}" required /></div>
          <div class="field"><label>الوقت</label><input type="time" name="time" value="${a.time}" required /></div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-outline" data-close>إلغاء</button>
          <button class="btn btn-primary">حفظ الموعد</button>
        </div>
      </form>
    `);
  }

  function lessonForm(appointmentId) {
    const a = Store.data.appointments.find((x) => x.id === appointmentId);
    const s = Store.student(a.studentId);
    Store.setStatus(appointmentId, "progress");
    showModal(`
      <form class="modal" id="lesson-form">
        <h3>الحصة الجارية: ${s.name}</h3>
        <input type="hidden" name="appointmentId" value="${a.id}" />
        <input type="hidden" name="studentId" value="${s.id}" />
        <p><b>التسميع</b></p>${rangeFields("recitation", s.recitation)}
        ${gradeFields("recitationGrade", "good")}
        <p><b>الحفظ الجديد</b></p>${rangeFields("newMem", s.newMem)}
        ${gradeFields("newMemGrade", "good")}
        <p><b>المراجعة</b></p>${rangeFields("review", s.review)}
        <div class="field full" style="margin-top:10px"><label>ملاحظتي على الأداء</label><textarea name="notes">${s.lastNote || ""}</textarea></div>
        <h3 style="margin-top:18px">مطلوب الحصة القادمة</h3>
        <p><b>تسميع قادم</b></p>${rangeFields("nextRecitation", s.newMem)}
        <p><b>حفظ قادم</b></p>${rangeFields("nextNewMem", { surah: s.newMem.surah, from: (s.newMem.to || 0) + 1, to: (s.newMem.to || 0) + 5 })}
        <p><b>مراجعة قادمة</b></p>${rangeFields("nextReview", s.review)}
        <div class="modal-actions">
          <button type="button" class="btn btn-danger" data-cancel-lesson="${a.id}">إلغاء الحصة</button>
          <button class="btn btn-success">إنهاء الحصة وحفظ</button>
        </div>
      </form>
    `);
  }

  function readRange(form, prefix) {
    return {
      surah: form[`${prefix}-surah`].value,
      from: Number(form[`${prefix}-from`].value) || "",
      to: Number(form[`${prefix}-to`].value) || ""
    };
  }

  async function finishLesson(form) {
    const payload = {
      appointmentId: form.appointmentId.value,
      studentId: form.studentId.value,
      date: Store.todayISO(),
      time: new Date().toTimeString().slice(0, 5),
      recitation: readRange(form, "recitation"),
      newMem: readRange(form, "newMem"),
      review: readRange(form, "review"),
      nextRecitation: readRange(form, "nextRecitation"),
      nextNewMem: readRange(form, "nextNewMem"),
      nextReview: readRange(form, "nextReview"),
      recitationGrade: (form.recitationGrade && form.recitationGrade.value) || "good",
      newMemGrade: (form.newMemGrade && form.newMemGrade.value) || "good",
      notes: form.notes.value
    };
    const student = Store.student(payload.studentId);
    payload.aiSummary = await AI.summarizeLesson(student, payload);
    Store.finishLesson(payload);
    const summaryHtml = `
      <div class="modal">
        <h3>ملخص الحصة</h3>
        <div class="ai-box">${payload.aiSummary}</div>
        <div class="list" style="margin-top:12px">
          <div>التسميع: ${Store.rangeText(payload.recitation)} <span class="badge ${payload.recitationGrade}">${(GRADES.find((g) => g.id === payload.recitationGrade) || {}).label}</span></div>
          <div>الحفظ: ${Store.rangeText(payload.newMem)} <span class="badge ${payload.newMemGrade}">${(GRADES.find((g) => g.id === payload.newMemGrade) || {}).label}</span></div>
          <div>المراجعة: ${Store.rangeText(payload.review)}</div>
        </div>
        <div class="modal-actions">
          <span></span>
          <button class="btn btn-primary" data-close>حفظ ومتابعة</button>
        </div>
      </div>
    `;
    render();
    showModal(summaryHtml);
  }

  function render() {
    const { path } = route();
    if (path === "#/students") studentsPage();
    else if (path === "#/appointments") appointmentsPage();
    else if (path === "#/lessons") lessonsPage();
    else if (path === "#/notes") notesPage();
    else if (path === "#/book") bookPage();
    else if (path === "#/assistant") assistantPage();
    else if (path === "#/reports") reportsPage();
    else if (path === "#/settings") settingsPage();
    else dashboard();
  }

  document.addEventListener("click", async (e) => {
    const t = e.target.closest("[data-select],[data-start],[data-copy],[data-close],[data-edit-student],[data-open-student],[data-edit-apt],[data-cancel-apt],[data-cancel-lesson],[data-history]");
    if (e.target.id === "add-student") studentForm();
    if (e.target.id === "add-apt") aptForm();
    if (e.target.id === "save-settings") {
      Store.data.settings.teacherName = document.getElementById("set-name").value;
      Store.data.settings.openaiKey = document.getElementById("set-key").value.trim();
      Store.data.settings.openaiModel = document.getElementById("set-model").value.trim();
      Store.persist();
      toast("تم حفظ الإعدادات");
      render();
    }
    if (e.target.id === "run-explain") {
      const student = Store.student(document.getElementById("ex-student").value);
      selectedStudentId = student.id;
      const surah = document.getElementById("ex-surah").value;
      const ayah = document.getElementById("ex-ayah").value;
      const box = document.getElementById("explain-result");
      box.innerHTML = `<p class="empty">جارٍ تجهيز الشرح...</p>`;
      const result = await AI.explain(student, surah, ayah);
      box.innerHTML = `
        <div class="card" style="margin-top:16px">
          <div class="level">شرح مناسب لـ ${student.name} · ${student.style}</div>
          <div class="verse">${result.verse ? "﴿" + result.verse + "﴾" : surah + " " + ayah}</div>
          <div class="ai-box">${result.body}</div>
          <div class="point-box"><b>نقطة مهمة لـ ${student.name}:</b><br>${result.point}</div>
        </div>`;
    }
    if (!t) return;
    if (t.dataset.select) {
      selectedStudentId = t.dataset.select;
      render();
    }
    if (t.dataset.start) lessonForm(t.dataset.start);
    if (t.dataset.copy) {
      navigator.clipboard.writeText(t.dataset.copy).then(() => toast("تم نسخ الرابط"));
    }
    if (t.hasAttribute("data-close")) {
      closeModal();
      render();
    }
    if (t.dataset.editStudent) studentForm(Store.student(t.dataset.editStudent));
    if (t.dataset.openStudent) location.hash = "#/students?id=" + t.dataset.openStudent;
    if (t.dataset.history) location.hash = "#/students?id=" + t.dataset.history;
    if (t.dataset.editApt) aptForm(Store.data.appointments.find((a) => a.id === t.dataset.editApt));
    if (t.dataset.cancelApt) {
      Store.setStatus(t.dataset.cancelApt, "cancelled");
      toast("تم إلغاء الموعد");
      render();
    }
    if (t.dataset.cancelLesson) {
      Store.setStatus(t.dataset.cancelLesson, "upcoming");
      closeModal();
      render();
    }
  });

  document.addEventListener("submit", async (e) => {
    if (e.target.id === "student-form") {
      e.preventDefault();
      const f = e.target;
      Store.upsertStudent({
        id: f.studentRecordId.value || undefined,
        name: f.name.value,
        phone: f.phone.value,
        level: f.level.value,
        style: f.style.value,
        meetLink: f.meetLink.value,
        currentSurah: f.currentSurah.value,
        lastAyah: Number(f.lastAyah.value) || 1,
        newMem: readRange(f, "newMem"),
        recitation: readRange(f, "recitation"),
        review: readRange(f, "review"),
        lastNote: f.lastNote.value
      });
      closeModal();
      toast("تم حفظ ملف الطالب");
      location.hash = "#/students";
      render();
    }
    if (e.target.id === "apt-form") {
      e.preventDefault();
      const f = e.target;
      Store.upsertAppointment({
        id: f.aptId.value || undefined,
        studentId: f.studentId.value,
        date: f.date.value,
        time: f.time.value
      });
      closeModal();
      toast("تم حفظ الموعد");
      render();
    }
    if (e.target.id === "lesson-form") {
      e.preventDefault();
      const btn = e.target.querySelector(".btn-success");
      btn.disabled = true;
      btn.textContent = "جارٍ الحفظ...";
      await finishLesson(e.target);
    }
  });

  document.addEventListener("change", (e) => {
    if (e.target.id === "student-search") {
      location.hash = "#/students?q=" + encodeURIComponent(e.target.value);
    }
  });

  window.addEventListener("hashchange", render);
  render();
})();
