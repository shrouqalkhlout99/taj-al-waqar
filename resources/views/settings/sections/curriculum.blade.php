@php
    $custom = old('curriculum_custom', \App\Models\Setting::json('curriculum_custom'));
    $items = [
        ['key' => 'curriculum_quran', 'icon' => '📖', 'title' => 'القرآن الكريم', 'manage' => route('students.index'), 'ready' => true],
        ['key' => 'curriculum_tajweed', 'icon' => '📚', 'title' => 'التجويد', 'manage' => null, 'ready' => false],
        ['key' => 'curriculum_aqidah', 'icon' => '🕌', 'title' => 'العقيدة', 'manage' => null, 'ready' => false],
        ['key' => 'curriculum_fiqh', 'icon' => '⚖️', 'title' => 'الفقه المبسط', 'manage' => null, 'ready' => false],
    ];
@endphp
<form class="card settings-card" method="post" action="{{ route('settings.update') }}" data-settings-form>
    @csrf
    <input type="hidden" name="section" value="curriculum">
    <h3>المناهج التعليمية</h3>
    <p class="muted mb-14">عطّلي منهجاً لإخفائه من القائمة فقط. بيانات الطلاب والحصص تبقى كما هي.</p>

    <div class="curriculum-grid">
        @foreach ($items as $item)
            <article class="curriculum-card">
                <div class="curriculum-head">
                    <span class="curriculum-icon">{{ $item['icon'] }}</span>
                    <strong>{{ $item['title'] }}</strong>
                </div>
                <x-setting-toggle :name="$item['key']" :checked="$s[$item['key']]" :title="$item['title']" />
                @if ($item['ready'] && $item['manage'])
                    <a class="btn btn-ghost" href="{{ $item['manage'] }}">إدارة المنهج</a>
                @else
                    <span class="btn btn-outline is-disabled" title="سيُضاف لاحقاً داخل تاج الوقار">إدارة المنهج</span>
                @endif
            </article>
        @endforeach

        @foreach ($custom as $i => $row)
            <article class="curriculum-card">
                <div class="curriculum-head">
                    <span class="curriculum-icon">➕</span>
                    <strong>{{ $row['name'] ?? 'منهج' }}</strong>
                </div>
                <label class="switch">
                    <input type="checkbox" name="custom_enabled[]" value="{{ $i }}" @checked(($row['enabled'] ?? '1') === '1')>
                    <span class="switch-ui" aria-hidden="true"></span>
                    <span class="sr-only">تفعيل {{ $row['name'] ?? '' }}</span>
                </label>
                <span class="muted">منهج مضاف — بلا صفحة منفصلة بعد</span>
            </article>
        @endforeach

        <article class="curriculum-card curriculum-add">
            <strong>➕ إضافة منهج</strong>
            <div class="field">
                <label for="new_curriculum_name">اسم المنهج</label>
                <input id="new_curriculum_name" name="new_curriculum_name" maxlength="80" placeholder="مثال: تفسير مبسّط">
            </div>
            <p class="muted">يُحفظ الاسم ويُفعّل. صفحة المنهج الكاملة ستُبنى لاحقاً دون حذف هذا التفضيل.</p>
        </article>
    </div>

    @include('settings.partials.save-bar')
</form>
