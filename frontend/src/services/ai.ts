import type { AiAskRequest, AiConversation, ApiResponse } from '@/types';
import { DatabaseType } from '@/types/enums';

import api from './api';

class AiService {
    public async getStatus() {
        const response = await api.get('/ai/status');
        return response;
    }

    public async askQuestion(data: AiAskRequest) {
        const response = await api.post('/ai/ask', data);
        return response;
    }

    public async explainError(sqlQuery: string, errorMessage: string, databaseType: DatabaseType) {
        const response = await api.post('/ai/explain-error', {
            sql_query: sqlQuery,
            error_message: errorMessage,
            database_type: databaseType,
        });
        return response;
    }

    public async optimizeQuery(sqlQuery: string, databaseType: DatabaseType, schema?: string) {
        const response = await api.post('/ai/optimize-query', {
            sql_query: sqlQuery,
            database_type: databaseType,
            schema: schema,
        });
        return response;
    }

    public async getExerciseHelp(exerciseId: number, question: string) {
        const response = await api.post('/ai/exercise-help', {
            exercise_id: exerciseId,
            question: question,
        });
        return response;
    }

    public async getConversations(limit: number = 50) {
        const response = await api.get('/ai/conversations', {
            params: { limit },
        });
        return response;
    }

    public async getAiAssistantStatus(): Promise<{
        enabled: boolean;
        provider: string;
    }> {
        try {
            const response = await this.getStatus();
            return {
                enabled: response.data.enabled,
                provider: response.data.provider,
            };
        } catch (error) {
            console.error('Failed to get AI assistant status:', error);
            return {
                enabled: false,
                provider: 'disabled',
            };
        }
    }
}

export default new AiService();
