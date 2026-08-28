<?php
declare(strict_types=1);

require_once __DIR__ . '/config/autoload.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$router = new Router();

// ── AUTH ──────────────────────────────────────────────────────────
$router->post('/auth/registro',    fn() => (new AuthController)->registro());
$router->post('/auth/login',       fn() => (new AuthController)->login());
$router->post('/auth/google',      fn() => (new AuthController)->google());
$router->post('/auth/refresh',     fn() => (new AuthController)->refresh());
$router->post('/auth/logout',      fn() => (new AuthController)->logout());
$router->post('/auth/push-token',  fn() => (new AuthController)->pushToken());

// ── ME ────────────────────────────────────────────────────────────
$router->get('/me',         fn() => (new MeController)->show());
$router->patch('/me',       fn() => (new MeController)->update());
$router->patch('/me/senha', fn() => (new MeController)->senha());

// ── TURMAS ────────────────────────────────────────────────────────
$router->get('/turmas',          fn()        => (new TurmasController)->index());
$router->post('/turmas',         fn()        => (new TurmasController)->store());
$router->patch('/turmas/{id}',   fn(int $id) => (new TurmasController)->update($id));

// ── ALUNOS ────────────────────────────────────────────────────────
$router->get('/alunos',                            fn()        => (new AlunosController)->index());
$router->post('/alunos',                           fn()        => (new AlunosController)->store());
$router->get('/alunos/{id}',                       fn(int $id) => (new AlunosController)->show($id));
$router->patch('/alunos/{id}',                     fn(int $id) => (new AlunosController)->update($id));
$router->get('/alunos/{id}/codigo-vinculo',        fn(int $id) => (new AlunosController)->getCodigo($id));
$router->post('/alunos/{id}/codigo-vinculo',       fn(int $id) => (new AlunosController)->gerarCodigo($id));

// ── VÍNCULOS ─────────────────────────────────────────────────────
$router->post('/vinculos',                         fn()        => (new VinculosController)->store());
$router->get('/vinculos',                          fn()        => (new VinculosController)->index());
$router->patch('/vinculos/{id}/avaliar',           fn(int $id) => (new VinculosController)->avaliar($id));
$router->patch('/vinculos/{id}/preferencia',       fn(int $id) => (new VinculosController)->preferencia($id));

// ── ATIVIDADES ───────────────────────────────────────────────────
$router->get('/atividades',                        fn()        => (new AtividadesController)->index());
$router->post('/atividades',                       fn()        => (new AtividadesController)->store());
$router->get('/atividades/{id}',                   fn(int $id) => (new AtividadesController)->show($id));
$router->patch('/atividades/{id}',                 fn(int $id) => (new AtividadesController)->update($id));
$router->delete('/atividades/{id}',                fn(int $id) => (new AtividadesController)->cancel($id));
$router->get('/atividades/{id}/leituras',          fn(int $id) => (new AtividadesController)->leituras($id));

// ── OCORRÊNCIAS ──────────────────────────────────────────────────
$router->post('/ocorrencias',                      fn()        => (new OcorrenciasController)->store());
$router->get('/ocorrencias',                       fn()        => (new OcorrenciasController)->index());
$router->get('/ocorrencias/{id}',                  fn(int $id) => (new OcorrenciasController)->show($id));
$router->patch('/ocorrencias/{id}/avaliar',        fn(int $id) => (new OcorrenciasController)->avaliar($id));

// ── USUÁRIOS (admin/secretaria) ───────────────────────────────────
$router->get('/usuarios',                          fn()        => (new UsuariosController)->index());
$router->get('/usuarios/{id}',                     fn(int $id) => (new UsuariosController)->show($id));
$router->patch('/usuarios/{id}',                   fn(int $id) => (new UsuariosController)->update($id));
$router->get('/usuarios/{id}/turmas',              fn(int $id) => (new UsuariosController)->turmas($id));
$router->post('/usuarios/{id}/turmas',             fn(int $id) => (new UsuariosController)->adicionarTurma($id));
$router->delete('/usuarios/{id}/turmas/{turmaId}', fn(int $id, int $turmaId) => (new UsuariosController)->removerTurma($id, $turmaId));

// ── CONFIGURAÇÕES ────────────────────────────────────────────────
$router->get('/configuracoes',     fn() => (new ConfiguracoesController)->show());
$router->patch('/configuracoes',   fn() => (new ConfiguracoesController)->update());

// ── DASHBOARD ────────────────────────────────────────────────────
$router->get('/dashboard', fn() => (new DashboardController)->index());

// ── Dispatch ─────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path if running in subdirectory (e.g. /agenda_elsacorradine/api)
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($basePath && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
}
$uri = $uri ?: '/';

$router->dispatch($method, $uri);
