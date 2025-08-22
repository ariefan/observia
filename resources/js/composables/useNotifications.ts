import { ref, computed, reactive } from 'vue';
import axios from 'axios';

// Backend-compatible notification interface
export interface Notification {
  id: string;
  user_id: string;
  type: string;
  title: string;
  message: string;
  action_required: boolean;
  action_status?: 'pending' | 'accepted' | 'rejected';
  acted_at?: string;
  read_at?: string;
  created_at: string;
  updated_at: string;
  // Frontend computed properties
  isRead?: boolean;
  priority?: 'low' | 'medium' | 'high' | 'urgent';
}

// Legacy interface for frontend compatibility
export interface LegacyNotification {
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
  private isLoading = ref(false);
  private lastFetch = ref<Date | null>(null);

  constructor() {
    this.loadNotifications();
    this.loadTemplatesFromStorage(); // Keep templates in localStorage for now
    this.initializeDefaultTemplates();
  }

  // Load notifications from backend API
  private async loadNotifications() {
    if (this.isLoading.value) return;
    
    this.isLoading.value = true;
    try {
      const response = await axios.get('/notifications');
      this.notifications.value = response.data.map((notification: any) => ({
        ...notification,
        isRead: !!notification.read_at,
        priority: this.inferPriorityFromType(notification.type)
      }));
      this.lastFetch.value = new Date();
    } catch (error) {
      console.error('Failed to load notifications from backend:', error);
      // Fallback to localStorage if backend fails
      this.loadFromStorage();
    } finally {
      this.isLoading.value = false;
    }
  }

  // Fallback method for localStorage (legacy support)
  private loadFromStorage() {
    try {
      const stored = localStorage.getItem('notifications');
      if (stored) {
        const parsed = JSON.parse(stored);
        this.notifications.value = parsed.map((n: any) => ({
          ...n,
          created_at: n.createdAt || new Date().toISOString(),
          isRead: n.isRead,
          message: n.description || n.message,
          type: n.type,
          title: n.title,
          action_required: false,
          user_id: 'local',
          updated_at: new Date().toISOString()
        }));
      }
    } catch (error) {
      console.error('Failed to load notifications from storage:', error);
    }
  }

  // Load templates from localStorage (keeping current functionality)
  private loadTemplatesFromStorage() {
    try {
      const storedTemplates = localStorage.getItem('notification-templates');
      if (storedTemplates) {
        this.templates.value = JSON.parse(storedTemplates);
      }
    } catch (error) {
      console.error('Failed to load templates from storage:', error);
    }
  }

  // Infer priority from notification type
  private inferPriorityFromType(type: string): 'low' | 'medium' | 'high' | 'urgent' {
    switch (type) {
      case 'error':
      case 'emergency':
        return 'urgent';
      case 'warning':
      case 'alert':
        return 'high';
      case 'reminder':
      case 'info':
        return 'medium';
      default:
        return 'low';
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
        .sort((a, b) => priorityOrder[b.priority || 'low'] - priorityOrder[a.priority || 'low'])
        .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
    });
  }

  get allTemplates() {
    return computed(() => this.templates.value);
  }

  get activeTemplates() {
    return computed(() => this.templates.value.filter(t => t.isActive));
  }

  // Actions - Backend integrated
  async addNotification(notification: { title: string; message: string; type: string; action_required?: boolean }) {
    try {
      // For super admin testing, create local notification if no backend
      if (notification.type.startsWith('test-') || notification.type === 'feeding-reminder') {
        const newNotification: Notification = {
          id: Date.now().toString() + Math.random().toString(36).substr(2, 9),
          user_id: 'local',
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
          isRead: false,
          priority: this.inferPriorityFromType(notification.type),
          action_required: notification.action_required || false,
          ...notification,
        };

        this.notifications.value.unshift(newNotification);
        this.saveToStorage();
        return newNotification;
      }

      // For real notifications, create via backend API
      const response = await axios.post('/notifications', notification);
      const newNotification = {
        ...response.data,
        isRead: !!response.data.read_at,
        priority: this.inferPriorityFromType(response.data.type)
      };
      
      this.notifications.value.unshift(newNotification);
      return newNotification;
    } catch (error) {
      console.error('Failed to create notification:', error);
      throw error;
    }
  }

  async markAsRead(id: string) {
    const notification = this.notifications.value.find(n => n.id === id);
    if (!notification) return;

    try {
      // If it's a local/test notification, just update locally
      if (notification.user_id === 'local') {
        notification.isRead = true;
        this.saveToStorage();
        return;
      }

      // For backend notifications, call API
      await axios.post(`/notifications/${id}/read`);
      notification.isRead = true;
      notification.read_at = new Date().toISOString();
    } catch (error) {
      console.error('Failed to mark notification as read:', error);
      // Still mark as read locally as fallback
      notification.isRead = true;
      this.saveToStorage();
    }
  }

  async markAllAsRead() {
    try {
      // Mark all backend notifications as read
      const backendNotifications = this.notifications.value.filter(n => n.user_id !== 'local');
      await Promise.all(
        backendNotifications.map(notification => 
          axios.post(`/notifications/${notification.id}/read`)
        )
      );
      
      // Update all notifications locally
      this.notifications.value.forEach(n => {
        n.isRead = true;
        if (!n.read_at) {
          n.read_at = new Date().toISOString();
        }
      });
      
      this.saveToStorage();
    } catch (error) {
      console.error('Failed to mark all notifications as read:', error);
      // Fallback to local update
      this.notifications.value.forEach(n => {
        n.isRead = true;
      });
      this.saveToStorage();
    }
  }

  async removeNotification(id: string) {
    const index = this.notifications.value.findIndex(n => n.id === id);
    if (index === -1) return;

    const notification = this.notifications.value[index];

    try {
      // If it's a backend notification, delete from backend
      if (notification.user_id !== 'local') {
        await axios.delete(`/notifications/${id}`);
      }
      
      // Remove from local state
      this.notifications.value.splice(index, 1);
      this.saveToStorage();
    } catch (error) {
      console.error('Failed to delete notification:', error);
      // Still remove locally as fallback
      this.notifications.value.splice(index, 1);
      this.saveToStorage();
    }
  }

  async clearAllNotifications() {
    try {
      // Delete all backend notifications
      const backendNotifications = this.notifications.value.filter(n => n.user_id !== 'local');
      await Promise.all(
        backendNotifications.map(notification => 
          axios.delete(`/notifications/${notification.id}`)
        )
      );
      
      // Clear local state
      this.notifications.value = [];
      this.saveToStorage();
    } catch (error) {
      console.error('Failed to clear all notifications:', error);
      // Fallback to local clear
      this.notifications.value = [];
      this.saveToStorage();
    }
  }

  // Refresh notifications from backend
  async refreshNotifications() {
    await this.loadNotifications();
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
      message: template.description,
      type: template.type,
      ...customData,
    });
  }

  scheduleNotification(templateId: string, scheduledFor: Date, customData?: Partial<Notification>) {
    const template = this.templates.value.find(t => t.id === templateId);
    if (!template) return null;

    return this.addNotification({
      title: template.title,
      message: template.description,
      type: template.type,
      ...customData,
    });
  }

  // Feeding reminder specific method
  createFeedingReminder(livestockNames: string[] = [], customMessage?: string) {
    const baseMessage = 'Saatnya memberikan pakan untuk ternak Anda.';
    const livestockMessage = livestockNames.length > 0 
      ? ` Ternak yang perlu diberi pakan: ${livestockNames.join(', ')}.`
      : '';
    const message = customMessage || (baseMessage + livestockMessage + ' Jangan lupa untuk mencatat jumlah dan jenis pakan yang diberikan.');

    return this.addNotification({
      title: 'Pengingat Pemberian Pakan',
      message,
      type: 'feeding-reminder',
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