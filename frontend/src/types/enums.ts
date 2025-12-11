export enum ExerciseStatus {
    NOT_STARTED = 'not_started',
    IN_PROGRESS = 'in_progress',
    COMPLETED = 'completed',
}

export enum DatabaseType {
    MYSQL = 'mysql',
    POSTGRESQL = 'postgresql',
    SQLITE = 'sqlite',
}

export enum AiContextType {
    GENERAL = 'general',
    SQL_ERROR = 'sql_error',
    SQL_OPTIMIZATION = 'sql_optimization',
    EXERCISE_HELP = 'exercise_help',
}
