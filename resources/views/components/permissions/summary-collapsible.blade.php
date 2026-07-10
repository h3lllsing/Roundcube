<div class="csum">
    <div
        class="csum-hd"
        role="button"
        tabindex="0"
        aria-expanded="false"
        @click="toggleSummary()"
        @keydown.enter.prevent="toggleSummary()"
    >
        <span class="arr">▶</span>
        <strong>Effective Access Summary</strong>
        <span x-text="summaryText">— Infrastructure (0 modified) · Operations (0 modified) · Administration (0 modified) · Integration (0 modified)</span>
    </div>
    <div class="csum-bd h" x-show="summaryOpen" x-cloak>
        <template x-for="(modules, catName) in categorizedModules" :key="catName">
            <div class="cs-cat">
                <div class="cs-cn" x-text="catName"></div>
                <template x-for="mod in modules" :key="mod.id">
                    <div class="cs-r" :class="{ o: mod.status === 'Modified' }">
                        <span x-text="mod.name"></span>
                        <span class="p-std" :class="'p-' + mod.presetClass" x-text="mod.presetLabel"></span>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
