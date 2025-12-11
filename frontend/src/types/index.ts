export interface User {
    id: number;
    email: string;
    username: string;
    createdAt: string;
}

export interface AuthResponse {
    status: string;
    message: string;
    token: string;
    user: User;
}

export interface ApiResponse<T = any> {
    status: string;
    message: string;
    data: T;
}

export interface ApiError {
    status: string;
    message: string;
    errors?: string[];
}

export type DatabaseType = 'mysql' | 'postgresql' | 'sqlite';

export interface SandboxQueryRequest {
    query: string;
}

export interface SandboxQueryResult {
    success: boolean;
    data?: any[];
    error?: string;
    affected_rows?: number;
    last_insert_id?: string;
    warnings: string[];
    execution_time_ms?: number;
}

export interface SandboxStructure {
    [tableName: string]: any[];
}

export interface Exercise {
    id: number;
    title: string;
    description: string;
    instructions: string;
    database_type: DatabaseType;
    difficulty: number;
    order_number: number;
}

export interface ExerciseProgress {
    id: number;
    status: 'not_started' | 'in_progress' | 'completed';
    user_query?: string;
    started_at: string;
    completed_at?: string;
    attempts?: number;
    score?: number;
}

export interface AiConversation {
    id: number;
    context_type: string;
    exercise_id?: number;
    database_type?: DatabaseType;
    user_message: string;
    ai_response: string;
    created_at: string;
    metadata: Record<string, any>;
}

export interface AiAskRequest {
    question: string;
    context_type?: string;
    exercise_id?: number;
    database_type?: DatabaseType;
    sql_query?: string;
    error_message?: string;
}
