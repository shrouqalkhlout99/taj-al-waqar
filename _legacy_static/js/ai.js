(function () {
  function gradeLabel(id) {
    return (GRADES.find((g) => g.id === id) || {}).label || "—";
  }

  function localSummary(student, lesson) {
    return `${student.name} سمّع ${Store.rangeText(lesson.recitation)} بمستوى «${gradeLabel(lesson.recitationGrade)}»، وحفظ ${Store.rangeText(lesson.newMem)} بمستوى «${gradeLabel(lesson.newMemGrade)}»، وراجع ${Store.rangeText(lesson.review)}. ${lesson.notes ? "ملاحظة المعلمة: " + lesson.notes : "لا توجد ملاحظات إضافية."} المطلوب للحصة القادمة: تسميع ${Store.rangeText(lesson.nextRecitation)}، وحفظ ${Store.rangeText(lesson.nextNewMem)}.`;
  }

  function localExplanation(student, surah, ayah, verseText) {
    const verse = verseText ? `﴿${verseText}﴾` : `سورة ${surah} الآية ${ayah}`;
    const style = student.style || "شرح مبسط";
    let body = "";
    if (style === "قصة مبسطة") {
      body = `تخيلي أنكِ تروين لـ${student.name} قصة قصيرة: الآية ${verse} تذكّرنا أن كلام الله نور يهدينا. اربطي المعنى بشيء يعيشه في يومه، ثم اسأليه: ماذا تعلمنا من هذه الآية؟`;
    } else if (style === "أسئلة ونقاش") {
      body = `ابدئي بقراءة الآية ${verse} ثم اسألي ${student.name}: من المتحدث؟ وما الطلب أو المعنى؟ وكيف نطبّقها اليوم؟`;
    } else if (style === "شرح تفصيلي") {
      body = `اشرحي المفردات ثم المعنى الإجمالي ثم الفائدة العملية للآية ${verse}، مع ربطها بما سبق أن حفظه في ${student.currentSurah}.`;
    } else {
      body = `بلّغي المعنى بجملة واحدة سهلة ثم أعيدي الآية ${verse} مع ${student.name} مرتين، لأن مستواه «${student.level}».`;
    }
    const point = student.lastNote
      ? `رابط بملاحظتك السابقة: ${student.lastNote}`
      : `ركّزي على تثبيت الآية وربطها بما وصل إليه في ${student.currentSurah}.`;
    return { body, point, verse: verseText || "" };
  }

  async function fetchVerse(surahName, ayah) {
    const index = QURAN_SURAHS.indexOf(surahName) + 1;
    if (index < 1) return "";
    try {
      const res = await fetch(`https://api.alquran.cloud/v1/ayah/${index}:${ayah}/ar`);
      const json = await res.json();
      return json.data && json.data.text ? json.data.text : "";
    } catch {
      return "";
    }
  }

  async function openai(messages) {
    const key = Store.data.settings.openaiKey;
    if (!key) return null;
    const res = await fetch("https://api.openai.com/v1/chat/completions", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: "Bearer " + key
      },
      body: JSON.stringify({
        model: Store.data.settings.openaiModel || "gpt-4o-mini",
        messages,
        temperature: 0.4
      })
    });
    if (!res.ok) throw new Error("تعذر الاتصال بنموذج الذكاء الاصطناعي");
    const json = await res.json();
    return json.choices[0].message.content.trim();
  }

  window.AI = {
    async summarizeLesson(student, lesson) {
      try {
        const ai = await openai([
          {
            role: "system",
            content: "أنت مساعدة لمعلمة قرآن. اكتبي ملخصاً عربياً قصيراً وواضحاً عن أداء الطالب بعد الحصة، بدون مقدمات."
          },
          {
            role: "user",
            content: JSON.stringify({
              student: student.name,
              level: student.level,
              recitation: Store.rangeText(lesson.recitation),
              recitationGrade: gradeLabel(lesson.recitationGrade),
              newMem: Store.rangeText(lesson.newMem),
              newMemGrade: gradeLabel(lesson.newMemGrade),
              review: Store.rangeText(lesson.review),
              notes: lesson.notes
            })
          }
        ]);
        return ai || localSummary(student, lesson);
      } catch {
        return localSummary(student, lesson);
      }
    },
    async explain(student, surah, ayah) {
      const verseText = await fetchVerse(surah, ayah);
      try {
        const ai = await openai([
          {
            role: "system",
            content: "أنت مساعدة لمعلمة قرآن. اشرحي الآية بأسلوب يناسب مستوى الطالب. أرجعي JSON فقط بالمفاتيح: body, point, verse"
          },
          {
            role: "user",
            content: JSON.stringify({
              student: student.name,
              level: student.level,
              style: student.style,
              lastNote: student.lastNote,
              surah,
              ayah,
              verseText
            })
          }
        ]);
        if (ai) {
          const parsed = JSON.parse(ai.replace(/```json|```/g, "").trim());
          return {
            body: parsed.body,
            point: parsed.point,
            verse: parsed.verse || verseText
          };
        }
      } catch {
        /* fallback */
      }
      return localExplanation(student, surah, ayah, verseText);
    }
  };
})();
