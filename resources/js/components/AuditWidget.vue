<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { History, Eye, User, Calendar } from 'lucide-vue-next';
import axios from 'axios';

interface Audit {
  id: number;
  user_name: string | null;
  event: string;
  event_name: string;
  created_at: string;
  user: {
    name: string;
    email: string;
  } | null;
}

interface Props {
  modelType: string;
  modelId: string;
  title?: string;
  limit?: number;
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Recent Activity',
  limit: 5,
});

const audits = ref<Audit[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

const loadAudits = async () => {
  try {
    loading.value = true;
    const response = await axios.get(route('audits.model', {
      modelType: props.modelType,
      modelId: props.modelId,
    }));
    
    // Take only the requested number of audits
    audits.value = response.data.audits.data.slice(0, props.limit);
  } catch (err) {
    console.error('Failed to load audit data:', err);
    error.value = 'Failed to load activity history';
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadAudits();
});

const getEventBadgeVariant = (event: string) => {
  switch (event) {
    case 'created':
      return 'default';
    case 'updated':
      return 'secondary';
    case 'deleted':
      return 'destructive';
    case 'restored':
      return 'outline';
    default:
      return 'secondary';
  }
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>

<template>
  <Card>
    <CardHeader class="pb-3">
      <CardTitle class="flex items-center space-x-2 text-lg">
        <History class="h-5 w-5" />
        <span>{{ title }}</span>
      </CardTitle>
      <CardDescription>
        Recent changes to this record
      </CardDescription>
    </CardHeader>
    <CardContent>
      <div v-if="loading" class="space-y-3">
        <div v-for="i in 3" :key="i" class="animate-pulse">
          <div class="flex items-center space-x-3">
            <div class="h-8 w-8 bg-gray-200 rounded-full"></div>
            <div class="flex-1 space-y-1">
              <div class="h-3 bg-gray-200 rounded w-3/4"></div>
              <div class="h-2 bg-gray-200 rounded w-1/2"></div>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="error" class="text-center py-4 text-muted-foreground">
        <p>{{ error }}</p>
      </div>

      <div v-else-if="audits.length === 0" class="text-center py-4 text-muted-foreground">
        <History class="h-8 w-8 mx-auto mb-2 text-gray-400" />
        <p>No activity recorded yet</p>
      </div>

      <div v-else class="space-y-3">
        <div 
          v-for="audit in audits" 
          :key="audit.id"
          class="flex items-start space-x-3 pb-3 border-b border-gray-100 last:border-b-0 last:pb-0"
        >
          <div class="flex-shrink-0 mt-1">
            <User class="h-4 w-4 text-muted-foreground" />
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <Badge :variant="getEventBadgeVariant(audit.event)" class="text-xs">
                  {{ audit.event_name }}
                </Badge>
                <span class="text-sm font-medium">
                  by {{ audit.user_name || 'System' }}
                </span>
              </div>
              <Link 
                :href="route('audits.show', { id: audit.id })"
                class="text-muted-foreground hover:text-foreground"
              >
                <Eye class="h-3 w-3" />
              </Link>
            </div>
            <div class="flex items-center space-x-1 mt-1">
              <Calendar class="h-3 w-3 text-muted-foreground" />
              <span class="text-xs text-muted-foreground">
                {{ formatDate(audit.created_at) }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="audits.length >= limit" class="pt-2">
          <Link :href="route('audits.index', { model: modelType })">
            <Button variant="outline" size="sm" class="w-full">
              <History class="mr-2 h-4 w-4" />
              View All Activity
            </Button>
          </Link>
        </div>
      </div>
    </CardContent>
  </Card>
</template>