<template>
  <nav
    class="app-sidebar"
    :class="[`app-sidebar--${theme}`, { 'app-sidebar--collapsed': collapsed }]"
  >
    <div class="app-sidebar__brand">
      <slot name="logo" />
    </div>
    <ul class="app-sidebar__nav">
      <template v-for="item in items" :key="item.id">
        <li
          v-if="item.type === 'section'"
          class="app-sidebar__section"
        >
          <span v-if="!collapsed">{{ item.label }}</span>
        </li>
        <li
          v-else
          class="app-sidebar__link"
          :class="{ 'app-sidebar__link--active': activeItem === item.id }"
          @click="$emit('select', item.id)"
        >
          <span v-if="item.icon" class="app-sidebar__link-icon" v-html="item.icon" />
          <span v-if="!collapsed" class="app-sidebar__link-label">{{ item.label }}</span>
          <span v-if="!collapsed && item.badge != null" class="app-sidebar__link-badge">{{ item.badge }}</span>
          <span v-if="activeItem === item.id" class="app-sidebar__link-indicator" />
        </li>
      </template>
    </ul>
    <div class="app-sidebar__bottom">
      <slot name="footer" />
    </div>
  </nav>
</template>

<script setup>
defineProps({
  /** Items: [{ id, label, icon?, badge?, type? }] (type='section' для заголовка группы) */
  items: { type: Array, required: true },
  activeItem: { type: String, default: '' },
  collapsed: { type: Boolean, default: false },
  /** 'dark' | 'light' */
  theme: { type: String, default: 'dark', validator: v => ['dark', 'light'].includes(v) },
})

defineEmits(['select'])
</script>

<style scoped>
.app-sidebar {
  display: flex;
  flex-direction: column;
  width: 240px;
  height: 100vh;
  padding: var(--space-5) var(--space-3);
  transition: width var(--duration-slow) var(--ease-out), background-color var(--duration-base) var(--ease-out);
  overflow: hidden;
}
.app-sidebar--collapsed {
  width: 60px;
  padding: var(--space-5) var(--space-1\.5);
}

/* ── Themes ── */
.app-sidebar--dark {
  background: var(--color-primary-dark);
  color: rgba(255, 255, 255, 0.65);
}
.app-sidebar--light {
  background: var(--color-white);
  color: var(--color-gray-600);
  border-right: 1px solid var(--color-gray-200);
}

/* ── Brand ── */
.app-sidebar__brand {
  padding: 0 var(--space-3) var(--space-6);
}
.app-sidebar--dark .app-sidebar__brand {
  color: var(--color-white);
}
.app-sidebar--collapsed .app-sidebar__brand {
  padding: 0 var(--space-1) var(--space-6);
  text-align: center;
}

/* ── Nav ── */
.app-sidebar__nav {
  flex: 1;
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 0;
  margin: 0;
  overflow-y: auto;
}

.app-sidebar__section {
  padding: var(--space-4) var(--space-3) var(--space-1);
  font-size: var(--text-2xs);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.app-sidebar--dark .app-sidebar__section {
  color: rgba(255, 255, 255, 0.4);
}
.app-sidebar--light .app-sidebar__section {
  color: var(--color-gray-400);
}

.app-sidebar__link {
  position: relative;
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-2\.5) var(--space-3);
  border-radius: var(--radius-lg);
  cursor: pointer;
  font-size: var(--text-sm);
  font-weight: 500;
  transition: all var(--duration-fast) var(--ease-out);
  -webkit-user-select: none;
  user-select: none;
}
.app-sidebar--collapsed .app-sidebar__link {
  justify-content: center;
  padding: var(--space-2\.5);
}

/* dark */
.app-sidebar--dark .app-sidebar__link {
  color: rgba(255, 255, 255, 0.65);
}
.app-sidebar--dark .app-sidebar__link:hover {
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.9);
}
.app-sidebar--dark .app-sidebar__link--active {
  background: rgba(255, 255, 255, 0.08);
  color: var(--color-white);
  font-weight: 600;
}
.app-sidebar--dark .app-sidebar__link-indicator {
  background: var(--color-primary-light);
}

/* light */
.app-sidebar--light .app-sidebar__link {
  color: var(--color-gray-600);
}
.app-sidebar--light .app-sidebar__link:hover {
  background: var(--color-gray-50);
  color: var(--color-gray-900);
}
.app-sidebar--light .app-sidebar__link--active {
  background: rgba(0, 50, 116, 0.05);
  color: var(--color-primary-dark);
  font-weight: 600;
}
.app-sidebar--light .app-sidebar__link-indicator {
  background: var(--color-primary-dark);
}

.app-sidebar__link-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  opacity: 0.7;
}
.app-sidebar__link--active .app-sidebar__link-icon {
  opacity: 1;
}

.app-sidebar__link-label {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.app-sidebar__link-badge {
  margin-left: auto;
  padding: 1px 7px;
  border-radius: var(--radius-full);
  font-size: var(--text-2xs);
  font-weight: 700;
  line-height: 1.5;
}
.app-sidebar--dark .app-sidebar__link-badge {
  background: rgba(255, 255, 255, 0.18);
  color: var(--color-white);
}
.app-sidebar--dark .app-sidebar__link--active .app-sidebar__link-badge {
  background: rgba(255, 255, 255, 0.25);
}
.app-sidebar--light .app-sidebar__link-badge {
  background: rgba(0, 50, 116, 0.1);
  color: var(--color-primary-dark);
}
.app-sidebar--light .app-sidebar__link--active .app-sidebar__link-badge {
  background: rgba(0, 50, 116, 0.2);
}

.app-sidebar__link-indicator {
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 16px;
  border-radius: 0 3px 3px 0;
}

/* ── Footer ── */
.app-sidebar__bottom {
  padding-top: var(--space-4);
  font-size: var(--text-xs);
}
.app-sidebar--dark .app-sidebar__bottom {
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}
.app-sidebar--light .app-sidebar__bottom {
  border-top: 1px solid var(--color-gray-200);
}
</style>
