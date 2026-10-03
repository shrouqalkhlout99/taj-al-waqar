(function () {
  const KEY = "quran-teacher-system-v1";
  const COLORS = ["#6b4bd6", "#1f9d6a", "#d97706", "#db2777", "#2563eb", "#0f766e"];

  function uid() {
    return Math.random().toString(36).slice(2, 10);
  }

  function todayISO() {
    const d = new Date();
    const z = (n) => String(n).padStart(2, "0");
    return `${d.getFullYear()}-${z(d.getMonth() + 1)}-${z(d.getDate())}`;
  }

  function seed() {
    const students = [
      {
        id: "s1",
        name: "أحمد محمد",
        phone: "0500000001",
        level: "متوسط",
        style: "قصة مبسطة",
        meetLink: "https://meet.google.com/abc-defg-hij",
        color: COLORS[0],
        currentSurah: "البقرة",
        lastAyah: 30,
        newMem: { surah: "البقرة", from: 31, to: 35 },
        recitation: { surah: "البقرة", from: 1, to: 20 },
        review: { surah: "الفاتحة", from: 1, to: 7 },
        lastNote: "خلط بين الآيتين 18 و19، يحتاج تثبيت التجويد في الغنة.",
        lastNoteDate: "2026-08-28"
      },
      {
        id: "s2",
        name: "سارة علي",
        phone: "0500000002",
        level: "مبتدئ",
        style: "شرح مبسط",
        meetLink: "https://meet.google.com/sara-meet-xyz",
        color: COLORS[1],
        currentSurah: "الفاتحة",
        lastAyah: 7,
        newMem: { surah: "البقرة", from: 1, to: 5 },
        recitation: { surah: "الفاتحة", from: 1, to: 7 },
        review: { surah: "الفاتحة", from: 1, to: 7 },
        lastNote: "نطق جيد مع بطء بسيط. شجعيها على التكرار اليومي.",
        lastNoteDate: "2026-08-30"
      },
      {
        id: "s3",
        name: "مريم أحمد",
        phone: "0500000003",
        level: "متقدم",
        style: "شرح تفصيلي",
        meetLink: "https://meet.google.com/mariam-quran",
        color: COLORS[2],
        currentSurah: "آل عمران",
        lastAyah: 40,
        newMem: { surah: "آل عمران", from: 41, to: 50 },
        recitation: { surah: "آل عمران", from: 20, to: 40 },
        review: { surah: "البقرة", from: 255, to: 257 },
        lastNote: "حفظ متين، ركزي على الوقف والابتداء.",
        lastNoteDate: "2026-08-31"
      },
      {
        id: "s4",
        name: "محمد خالد",
        phone: "0500000004",
        level: "متوسط",
        style: "أسئلة ونقاش",
        meetLink: "https://meet.google.com/mk-quran-class",
        color: COLORS[3],
        currentSurah: "يس",
        lastAyah: 12,
        newMem: { surah: "يس", from: 13, to: 27 },
        recitation: { surah: "يس", from: 1, to: 12 },
        review: { surah: "الملك", from: 1, to: 10 },
        lastNote: "يحتاج مراجعة مخارج الحروف الحلقية.",
        lastNoteDate: "2026-09-01"
      }
    ];

    const date = todayISO();
    const appointments = [
      { id: "a1", studentId: "s1", date, time: "16:00", status: "upcoming" },
      { id: "a2", studentId: "s2", date, time: "17:00", status: "upcoming" },
      { id: "a3", studentId: "s3", date, time: "18:00", status: "upcoming" },
      { id: "a4", studentId: "s4", date, time: "19:00", status: "upcoming" }
    ];

    return {
      settings: { teacherName: "معلمة قرآن", openaiKey: "", openaiModel: "gpt-4o-mini" },
      students,
      appointments,
      lessons: []
    };
  }

  function load() {
    try {
      const raw = localStorage.getItem(KEY);
      if (!raw) {
        const data = seed();
        save(data);
        return data;
      }
      return JSON.parse(raw);
    } catch {
      const data = seed();
      save(data);
      return data;
    }
  }

  function save(data) {
    localStorage.setItem(KEY, JSON.stringify(data));
  }

  const Store = {
    data: load(),
    persist() {
      save(this.data);
    },
    todayISO,
    uid,
    student(id) {
      return this.data.students.find((s) => s.id === id);
    },
    rangeText(r) {
      if (!r || !r.surah) return "—";
      return `${r.surah} ${r.from || ""}–${r.to || ""}`.trim();
    },
    requirement(student) {
      if (!student) return "—";
      return `تسميع: ${this.rangeText(student.recitation)}`;
    },
    todayAppointments() {
      const d = todayISO();
      return this.data.appointments
        .filter((a) => a.date === d)
        .sort((a, b) => a.time.localeCompare(b.time));
    },
    upsertStudent(student) {
      if (!student.id) student.id = uid();
      if (!student.color) student.color = COLORS[this.data.students.length % COLORS.length];
      const i = this.data.students.findIndex((s) => s.id === student.id);
      if (i >= 0) this.data.students[i] = { ...this.data.students[i], ...student };
      else this.data.students.push(student);
      this.persist();
      return student.id;
    },
    deleteStudent(id) {
      this.data.students = this.data.students.filter((s) => s.id !== id);
      this.data.appointments = this.data.appointments.filter((a) => a.studentId !== id);
      this.persist();
    },
    upsertAppointment(apt) {
      if (!apt.id) apt.id = uid();
      if (!apt.status) apt.status = "upcoming";
      const i = this.data.appointments.findIndex((a) => a.id === apt.id);
      if (i >= 0) this.data.appointments[i] = { ...this.data.appointments[i], ...apt };
      else this.data.appointments.push(apt);
      this.persist();
      return apt.id;
    },
    setStatus(id, status) {
      const a = this.data.appointments.find((x) => x.id === id);
      if (a) {
        a.status = status;
        this.persist();
      }
    },
    finishLesson(payload) {
      const lesson = { id: uid(), createdAt: new Date().toISOString(), ...payload };
      this.data.lessons.unshift(lesson);
      const student = this.student(payload.studentId);
      if (student) {
        student.currentSurah = payload.newMem.surah || student.currentSurah;
        student.lastAyah = Number(payload.newMem.to) || student.lastAyah;
        student.newMem = payload.nextNewMem || payload.newMem;
        student.recitation = payload.nextRecitation || payload.recitation;
        student.review = payload.nextReview || payload.review;
        student.lastNote = payload.notes || student.lastNote;
        student.lastNoteDate = todayISO();
      }
      if (payload.appointmentId) this.setStatus(payload.appointmentId, "done");
      this.persist();
      return lesson;
    }
  };

  window.Store = Store;
})();
