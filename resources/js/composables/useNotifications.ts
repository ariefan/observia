import { ref, computed, reactive } from 'vue';

export interface Notification {
  id: string;
  title: string;
  description: string;
  type: 'info' | 'success' | 'warning' | 'error' | 'reminder';
  priority: 'low' | 'medium' | 'high' | 'urgent';
  isRead: boolean;
  createdAt: Date;
  scheduledFor?: Date;
  targetUsers?: string[];
  metadata?: {
    relatedEntity?: string;
    entityId?: string | number;
    actionUrl?: string;
  };
}

export interface NotificationTemplate {
  id: string;
  name: string;
  title: string;
  description: string;
  type: 'info' | 'success' | 'warning' | 'error' | 'reminder';
  priority: 'low' | 'medium' | 'high' | 'urgent';
  isActive: boolean;
  schedule?: {
    type: 'immediate' | 'daily' | 'weekly' | 'monthly' | 'custom';
    time?: string; // HH:mm format
    days?: number[]; // 0-6 for weekly, 1-31 for monthly
    customCron?: string;
  };
  conditions?: {
    farmId?: string;
    userRole?: string[];
    livestockStatus?: string[];
  };
}

class NotificationStore {
  private notifications = ref<Notification[]>([]);
  private templates = ref<NotificationTemplate[]>([]);

  constructor() {
    this.loadFromStorage();
    this.initializeDefaultTemplates();
  }

  private loadFromStorage() {
    try {
      const stored = localStorage.getItem('notifications');
      if (stored) {
        const parsed = JSON.parse(stored);
        this.notifications.value = parsed.map((n: any) => ({
          ...n,
          createdAt: new Date(n.createdAt),
          scheduledFor: n.scheduledFor ? new Date(n.scheduledFor) : undefined,
        }));
      }

      const storedTemplates = localStorage.getItem('notification-templates');
      if (storedTemplates) {
        this.templates.value = JSON.parse(storedTemplates);
      }
    } catch (error) {
      console.error('Failed to load notifications from storage:', error);
    }
  }

  private saveToStorage() {
    try {
      localStorage.setItem('notifications', JSON.stringify(this.notifications.value));
      localStorage.setItem('notification-templates', JSON.stringify(this.templates.value));
    } catch (error) {
      console.error('Failed to save notifications to storage:', error);
    }
  }

  private initializeDefaultTemplates() {
    if (this.templates.value.length === 0) {
      const defaultTemplates: NotificationTemplate[] = [
        {
          id: 'feeding-reminder',
          name: 'Pengingat Pemberian Pakan',
          title: 'Waktunya Memberi Pakan',
          description: 'Saatnya memberikan pakan untuk ternak Anda. Jangan lupa untuk mencatat jumlah dan jenis pakan yang diberikan.',
          type: 'reminder',
          priority: 'medium',
          isActive: true,
          schedule: {
            type: 'daily',
            time: '06:00',
          },
          conditions: {
            userRole: ['admin', 'farm_manager', 'worker'],
          },
        },
        {
          id: 'health-checkup',
          name: 'Pengingat Pemeriksaan Kesehatan',
          title: 'Pemeriksaan Kesehatan Ternak',
          description: 'Lakukan pemeriksaan kesehatan rutin pada ternak Anda untuk memastikan kondisi yang optimal.',
          type: 'reminder',
          priority: 'high',
          isActive: false,
          schedule: {
            type: 'weekly',
            time: '08:00',
            days: [1], // Monday
          },
        },
        {
          id: 'milking-reminder',
          name: 'Pengingat Pemerahan',
          title: 'Waktu Pemerahan',
          description: 'Saatnya melakukan pemerahan. Pastikan untuk mencatat volume susu yang dihasilkan.',
          type: 'reminder',
          priority: 'high',
          isActive: true,
          schedule: {
            type: 'daily',
            time: '05:00',
          },
          conditions: {
            livestockStatus: ['lactating'],
          },
        },
      ];

      this.templates.value = defaultTemplates;
      this.saveToStorage();
    }
  }

  // Getters
  get allNotifications() {
    return computed(() => this.notifications.value);
  }

  get unreadNotifications() {
    return computed(() => this.notifications.value.filter(n => !n.isRead));
  }

  get notificationsByPriority() {
    return computed(() => {
      const priorityOrder = { urgent: 4, high: 3, medium: 2, low: 1 };
      return this.notifications.value
        .sort((a, b) => priorityOrder[b.priority] - priorityOrder[a.priority])
        .sort((a, b) => b.createdAt.getTime() - a.createdAt.getTime());
    });
  }

  get allTemplates() {
    return computed(() => this.templates.value);
  }

  get activeTemplates() {
    return computed(() => this.templates.value.filter(t => t.isActive));
  }

  // Actions
  addNotification(notification: Omit<Notification, 'id' | 'createdAt' | 'isRead'>) {
    const newNotification: Notification = {
      id: Date.now().toString() + Math.random().toString(36).substr(2, 9),
      createdAt: new Date(),
      isRead: false,
      ...notification,
    };

    this.notifications.value.unshift(newNotification);
    this.saveToStorage();
    return newNotification;
  }

  markAsRead(id: string) {
    const notification = this.notifications.value.find(n => n.id === id);
    if (notification) {
      notification.isRead = true;
      this.saveToStorage();
    }
  }

  markAllAsRead() {
    this.notifications.value.forEach(n => n.isRead = true);
    this.saveToStorage();
  }

  removeNotification(id: string) {
    const index = this.notifications.value.findIndex(n => n.id === id);
    if (index !== -1) {
      this.notifications.value.splice(index, 1);
      this.saveToStorage();
    }
  }

  clearAllNotifications() {
    this.notifications.value = [];
    this.saveToStorage();
  }

  // Template management
  addTemplate(template: Omit<NotificationTemplate, 'id'>) {
    const newTemplate: NotificationTemplate = {
      id: Date.now().toString() + Math.random().toString(36).substr(2, 9),
      ...template,
    };

    this.templates.value.push(newTemplate);
    this.saveToStorage();
    return newTemplate;
  }

  updateTemplate(id: string, updates: Partial<NotificationTemplate>) {
    const index = this.templates.value.findIndex(t => t.id === id);
    if (index !== -1) {
      this.templates.value[index] = { ...this.templates.value[index], ...updates };
      this.saveToStorage();
    }
  }

  removeTemplate(id: string) {
    const index = this.templates.value.findIndex(t => t.id === id);
    if (index !== -1) {
      this.templates.value.splice(index, 1);
      this.saveToStorage();
    }
  }

  // Utility methods
  createFromTemplate(templateId: string, customData?: Partial<Notification>) {
    const template = this.templates.value.find(t => t.id === templateId);
    if (!template) return null;

    return this.addNotification({
      title: template.title,
      description: template.description,
      type: template.type,
      priority: template.priority,
      ...customData,
    });
  }

  scheduleNotification(templateId: string, scheduledFor: Date, customData?: Partial<Notification>) {
    const template = this.templates.value.find(t => t.id === templateId);
    if (!template) return null;

    return this.addNotification({
      title: template.title,
      description: template.description,
      type: template.type,
      priority: template.priority,
      scheduledFor,
      ...customData,
    });
  }

  // Feeding reminder specific method
  createFeedingReminder(livestockNames: string[] = [], customMessage?: string) {
    const baseMessage = 'Saatnya memberikan pakan untuk ternak Anda.';
    const livestockMessage = livestockNames.length > 0 
      ? ` Ternak yang perlu diberi pakan: ${livestockNames.join(', ')}.`
      : '';
    const description = customMessage || (baseMessage + livestockMessage + ' Jangan lupa untuk mencatat jumlah dan jenis pakan yang diberikan.');

    return this.addNotification({
      title: 'Pengingat Pemberian Pakan',
      description,
      type: 'reminder',
      priority: 'medium',
      metadata: {
        relatedEntity: 'feeding',
        actionUrl: '/feedings',
      },
    });
  }
}

// Singleton instance
const notificationStore = new NotificationStore();

export function useNotifications() {
  return {
    // Getters
    notifications: notificationStore.allNotifications,
    unreadNotifications: notificationStore.unreadNotifications,
    notificationsByPriority: notificationStore.notificationsByPriority,
    templates: notificationStore.allTemplates,
    activeTemplates: notificationStore.activeTemplates,
    
    // Actions
    addNotification: notificationStore.addNotification.bind(notificationStore),
    markAsRead: notificationStore.markAsRead.bind(notificationStore),
    markAllAsRead: notificationStore.markAllAsRead.bind(notificationStore),
    removeNotification: notificationStore.removeNotification.bind(notificationStore),
    clearAllNotifications: notificationStore.clearAllNotifications.bind(notificationStore),
    
    // Template management
    addTemplate: notificationStore.addTemplate.bind(notificationStore),
    updateTemplate: notificationStore.updateTemplate.bind(notificationStore),
    removeTemplate: notificationStore.removeTemplate.bind(notificationStore),
    
    // Utility methods
    createFromTemplate: notificationStore.createFromTemplate.bind(notificationStore),
    scheduleNotification: notificationStore.scheduleNotification.bind(notificationStore),
    createFeedingReminder: notificationStore.createFeedingReminder.bind(notificationStore),
  };
}