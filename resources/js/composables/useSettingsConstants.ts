export const SETTING_TYPES = [
  { value: 'text', label: 'Text' },
  { value: 'number', label: 'Number' },
  { value: 'boolean', label: 'Boolean' },
  { value: 'textarea', label: 'Textarea' },
  { value: 'select', label: 'Select' }
] as const;

export const SETTING_CATEGORIES = [
  { value: 'general', label: 'General' },
  { value: 'telegram', label: 'Telegram' },
  { value: 'backup', label: 'Backup' },
  { value: 'notifications', label: 'Notifications' },
  { value: 'system', label: 'System' }
] as const;

export const BOOLEAN_OPTIONS = [
  { value: 'true', label: 'True' },
  { value: 'false', label: 'False' }
] as const;

export const CATEGORY_TITLES: Record<string, string> = {
  telegram: 'Telegram Bot',
  backup: 'Backup & Restore',
  general: 'Pengaturan Umum',
  notifications: 'Notifikasi',
  system: 'Sistem',
};

export const CATEGORY_DESCRIPTIONS: Record<string, string> = {
  telegram: 'Konfigurasi bot Telegram untuk notifikasi dan integrasi',
  backup: 'Pengaturan backup otomatis database dan file ke Google Drive',
  general: 'Pengaturan aplikasi secara umum',
  notifications: 'Pengaturan notifikasi sistem',
  system: 'Konfigurasi sistem dan performa',
};

export const getCategoryTitle = (category: string): string => {
  return CATEGORY_TITLES[category] || category.charAt(0).toUpperCase() + category.slice(1);
};

export const getCategoryDescription = (category: string): string => {
  return CATEGORY_DESCRIPTIONS[category] || `Pengaturan ${category}`;
};