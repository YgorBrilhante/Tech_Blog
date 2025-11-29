from django.contrib.auth.decorators import login_required
from django.shortcuts import render , redirect
from django.contrib.auth import authenticate, login, logout
from django.contrib.auth.models import User
from django.contrib import messages
import requests
from django.http import HttpResponse


@login_required
def dashboard(request):
    backend_id = request.session.get('backend_user_id')
    if backend_id is None:
        backend_id = get_or_create_backend_user_id(request.user)
        if backend_id:
            request.session['backend_user_id'] = backend_id

    posts = []
    if backend_id:
        try:
            response = requests.get(f"{API_URL_POSTS}?user_id={backend_id}", timeout=5)
            response.raise_for_status()
            posts = response.json().get('posts', [])
            posts = [p for p in posts if str(p.get('user_id')) == str(backend_id)]
        except requests.exceptions.RequestException:
            messages.error(request, "Erro ao carregar seus posts da API.")

    return render(request, 'dashboard.html', { 'posts': posts })

API_URL_USERS = 'http://localhost/ProjetoBlog/backend/web/users'
API_URL_POSTS = 'http://localhost/ProjetoBlog/backend/web/posts'
API_URL_POST_TAGS = 'http://localhost/ProjetoBlog/backend/web/posttags'
API_URL_COMMENTS = 'http://localhost/ProjetoBlog/backend/web/comments'
API_URL_TAGS = 'http://localhost/ProjetoBlog/backend/web/tags'

@login_required
def index(request):
    selected_tag_id = request.GET.get('tag_id')
    tags = []
    try:
        t_resp = requests.get(API_URL_TAGS, timeout=5)
        if t_resp.ok:
            tags = t_resp.json().get('tags', [])
    except requests.exceptions.RequestException:
        pass
    try:
        posts = []
        if selected_tag_id:
            endpoints = [
                (f"{API_URL_POST_TAGS}/{selected_tag_id}", None),
                (API_URL_POST_TAGS, {'tag_id': selected_tag_id}),
                ("http://localhost/ProjetoBlog/backend/web/post_tags/%s" % selected_tag_id, None),
                ("http://localhost/ProjetoBlog/backend/web/post_tags", {'tag_id': selected_tag_id}),
            ]
            for url, params in endpoints:
                try:
                    r = requests.get(url, params=params, timeout=5)
                    if r.ok:
                        data = r.json()
                        posts = data.get('posts', [])
                        if posts:
                            break
                except requests.exceptions.RequestException:
                    continue
        if not posts:
            response = requests.get(API_URL_POSTS, timeout=5)
            response.raise_for_status()
            posts = response.json().get('posts', [])
    except requests.exceptions.RequestException:
        posts = []
        messages.error(request, "Erro ao carregar posts da API.")
    user_name_map = {}
    try:
        u_resp = requests.get(API_URL_USERS, timeout=5)
        if u_resp.ok:
            users = u_resp.json().get('users', [])
            user_name_map = {str(u.get('id')): u.get('name') for u in users}
    except requests.exceptions.RequestException:
        pass
    for p in posts:
        uid = str(p.get('user_id'))
        p['user_name'] = user_name_map.get(uid, uid)
    return render(request, 'Index.html', {'posts': posts, 'tags': tags, 'selected_tag_id': selected_tag_id})

from django.contrib.auth.models import User

def login_view(request):
    storage = messages.get_messages(request)
    for _ in storage:
        pass
    if request.method == "POST":
        email = request.POST.get("email")
        password = request.POST.get("password")

        # buscar usuário pelo email
        try:
            user_obj = User.objects.get(email=email)
            username = user_obj.username
        except User.DoesNotExist:
            messages.error(request, "Email ou senha inválidos", extra_tags="login")
            return render(request, "login.html")

        # autenticar pelo username real
        user = authenticate(request, username=username, password=password)

        if user is not None:
            login(request, user)
            backend_id = get_or_create_backend_user_id(user)
            if backend_id:
                request.session['backend_user_id'] = backend_id
            return redirect("index")

        messages.error(request, "Email ou senha inválidos", extra_tags="login")

    return render(request, "login.html")


def register_view(request):
    if request.method == "POST":
        username = request.POST.get("username")
        email = request.POST.get("email")
        password = request.POST.get("password")
        password2 = request.POST.get("password2")

        # 1) verificar senhas
        if password != password2:
            messages.error(request, "As senhas não coincidem.")
            return render(request, "register.html")

        # 2) verificar email duplicado
        if User.objects.filter(email=email).exists():
            messages.error(request, "Este email já está em uso.")
            return render(request, "register.html")

        # 3) verificar username duplicado
        if User.objects.filter(username=username).exists():
            messages.error(request, "Este nome já está em uso.")
            return render(request, "register.html")

        # 4) criar usuário
        user = User.objects.create_user(
            username=username,
            email=email,
            password=password
        )

        messages.success(
            request,
            "Conta criada com sucesso! Faça login para continuar.",
            extra_tags="login"
        )
        return redirect("login")

    return render(request, "register.html")

@login_required
def create_post(request):
    if request.method == 'GET':
        tags = []
        try:
            resp = requests.get(API_URL_TAGS, timeout=5)
            if resp.ok:
                tags = resp.json().get('tags', [])
        except requests.exceptions.RequestException:
            pass
        return render(request, "create_post.html", {'tags': tags})
    return render(request, "create_post.html")

@login_required
def post(request):
    return render(request, "post_detail.html")

@login_required
def profile_view(request):
    return render(request, 'profile.html', {'user': request.user})


def logout_view(request):
    logout(request)
    return redirect('login')
@login_required
def profile_edit(request):
    user = request.user
    if request.method == 'POST':
        name = request.POST.get('name')
        email = request.POST.get('email')
        password = request.POST.get('password')
        confirm = request.POST.get('confirm')

        # Atualiza nome e email
        user.username = name
        user.email = email

        # Atualiza senha se preenchida
        if password:
            if password == confirm:
                user.set_password(password)
            else:
                messages.error(request, "As senhas não coincidem!")
                return redirect('profile_edit')

        user.save()
        messages.success(request, "Perfil atualizado com sucesso!")
        return redirect('profile')

    return render(request, 'profile_edit.html', {'user': user})

@login_required
def profile_delete(request):
    if request.method == 'POST':
        user = request.user
        logout(request)
        user.delete()
        messages.success(request, "Conta excluída com sucesso!")
        return redirect('login')
    return render(request, 'profile_delete.html')


#chama API REST

API_URL = 'http://localhost:8000/users'  # URL da API PHPixie

@login_required
def users_list(request):
    try:
        response = requests.get(API_URL_USERS, timeout=5)
        response.raise_for_status()
        users = response.json().get('users', [])
    except requests.exceptions.RequestException:
        users = []
        messages.error(request, "Erro ao carregar usuários da API.")
    return render(request, 'users_list.html', {'users': users})

@login_required
def user_detail(request, id):
    try:
        response = requests.get(f"{API_URL_USERS}?id={id}", timeout=5)
        response.raise_for_status()
        user = response.json().get('user', {})
    except requests.exceptions.RequestException:
        user = {}
        messages.error(request, "Erro ao carregar usuário da API.")
    return render(request, 'user_detail.html', {'user': user})

def user_create(request):
    if request.method == 'POST':
        data = {
            'name': request.POST.get('name'),
            'email': request.POST.get('email'),
            'password': request.POST.get('password')
        }
        try:
            requests.post(API_URL_USERS, data=data, timeout=5)
        except requests.exceptions.RequestException:
            messages.error(request, "Erro ao criar usuário na API.")
        return redirect('users_list')
    return render(request, 'register.html')

@login_required
def user_update(request, id):
    if request.method == 'POST':
        data = {
            'name': request.POST.get('name'),
            'email': request.POST.get('email'),
            'password': request.POST.get('password')
        }
        try:
            requests.put(f"{API_URL_USERS}?id={id}", data=data, timeout=5)
        except requests.exceptions.RequestException:
            messages.error(request, "Erro ao atualizar usuário na API.")
        return redirect('users_list')

    try:
        response = requests.get(f"{API_URL_USERS}?id={id}", timeout=5)
        response.raise_for_status()
        user = response.json().get('user', {})
    except requests.exceptions.RequestException:
        user = {}
    return render(request, 'register.html', {'user': user})

@login_required
def user_delete(request, id):
    try:
        requests.delete(f"{API_URL_USERS}?id={id}", timeout=5)
    except requests.exceptions.RequestException:
        messages.error(request, "Erro ao deletar usuário na API.")
    return redirect('users_list')

#Posts
@login_required
def post_detail(request, post_id):
    if request.method == 'POST':
        content = request.POST.get('content')
        backend_id = request.session.get('backend_user_id')
        if backend_id is None:
            backend_id = get_or_create_backend_user_id(request.user)
            if backend_id:
                request.session['backend_user_id'] = backend_id
        if content and backend_id:
            data = {
                'post_id': post_id,
                'user_id': backend_id,
                'content': content,
            }
            try:
                resp = requests.post(API_URL_COMMENTS, data=data, timeout=5)
                resp.raise_for_status()
                messages.success(request, "Comentário enviado!")
            except requests.exceptions.RequestException:
                messages.error(request, "Erro ao enviar comentário.")
        return redirect('post_detail', post_id=post_id)

    post = None
    comments = []
    user_name_map = {}
    try:
        response = requests.get(f'{API_URL_POSTS}/show/{post_id}', timeout=5)
        response.raise_for_status()
        data_json = response.json()
        post = data_json.get('post', None)
    except requests.exceptions.RequestException:
        messages.error(request, "Erro ao carregar o post.")

    try:
        c_resp = requests.get(f'{API_URL_COMMENTS}?post_id={post_id}', timeout=5)
        if c_resp.ok:
            comments = c_resp.json().get('comments', [])
    except requests.exceptions.RequestException:
        messages.error(request, "Erro ao carregar comentários.")

    try:
        u_resp = requests.get(API_URL_USERS, timeout=5)
        if u_resp.ok:
            users = u_resp.json().get('users', [])
            user_name_map = {str(u.get('id')): u.get('name') for u in users}
    except requests.exceptions.RequestException:
        pass

    for c in comments:
        uid = str(c.get('user_id'))
        c['user_name'] = user_name_map.get(uid, uid)

    if post:
        puid = str(post.get('user_id'))
        post['user_name'] = user_name_map.get(puid, puid)

    backend_id = request.session.get('backend_user_id')
    return render(request, 'post_detail.html', {'post': post, 'comments': comments, 'backend_user_id': backend_id})


# DETALHE DE UM POST
# CRIAR POST
@login_required
def create_post(request):
    if request.method == 'POST':
        data = {
            'titulo': request.POST.get('titulo'),
            'conteudo': request.POST.get('conteudo'),
        }
        backend_id = request.session.get('backend_user_id')
        if backend_id is None:
            backend_id = get_or_create_backend_user_id(request.user)
            if backend_id:
                request.session['backend_user_id'] = backend_id
        if backend_id:
            data['user_id'] = backend_id
        try:
            response = requests.post(API_URL_POSTS, data=data, timeout=5)
            response.raise_for_status()
            post_obj = response.json().get('post', {})
            tag_id = request.POST.get('tag_id')
            if tag_id and post_obj.get('id'):
                try:
                    at = requests.post(API_URL_POST_TAGS, data={'post_id': post_obj['id'], 'tag_id': tag_id}, timeout=5)
                    at.raise_for_status()
                except requests.exceptions.RequestException:
                    try:
                        alt_url = 'http://localhost/ProjetoBlog/backend/web/post_tags'
                        at2 = requests.post(alt_url, data={'post_id': post_obj['id'], 'tag_id': tag_id}, timeout=5)
                        at2.raise_for_status()
                    except requests.exceptions.RequestException:
                        pass
            messages.success(request, "Post criado com sucesso!")
            return redirect('index')
        except requests.exceptions.RequestException:
            messages.error(request, "Erro ao criar post.")
    tags = []
    try:
        resp = requests.get(API_URL_TAGS, timeout=5)
        if resp.ok:
            tags = resp.json().get('tags', [])
    except requests.exceptions.RequestException:
        pass
    return render(request, 'create_post.html', {'tags': tags})


# ATUALIZAR POST
@login_required
def update_post(request, post_id):
    if request.method == 'POST':
        titulo = request.POST.get('titulo')
        conteudo = request.POST.get('conteudo')
        data = {'titulo': titulo, 'conteudo': conteudo}
        params = {'titulo': titulo, 'conteudo': conteudo}
        try:
            # 1) PUT /posts/<id> com parâmetros na query (compatível com parsers que não leem body em PUT)
            try:
                response = requests.put(f'{API_URL_POSTS}/{post_id}', params=params, timeout=5)
                response.raise_for_status()
                resp_json = response.json()
            except requests.exceptions.RequestException:
                resp_json = {}
            # 2) PUT /posts?id=<id> com parâmetros na query
            if not resp_json.get('updated'):
                try:
                    alt = requests.put(f'{API_URL_POSTS}', params={**params, 'id': post_id}, timeout=5)
                    alt.raise_for_status()
                    resp_json = alt.json()
                except requests.exceptions.RequestException:
                    resp_json = {}
            if resp_json.get('updated'):
                messages.success(request, "Post atualizado com sucesso!")
                return redirect('post_detail', post_id=post_id)
            else:
                messages.error(request, "Atualização não aplicada.")
        except requests.exceptions.RequestException:
            messages.error(request, "Erro ao atualizar post.")

    # GET: buscar dados atuais para preencher o form
    try:
        response = requests.get(f'{API_URL_POSTS}/show/{post_id}', timeout=5)
        response.raise_for_status()
        data_json = response.json()
        post = data_json.get('post')
    except requests.exceptions.RequestException:
        post = None
        messages.error(request, "Erro ao carregar post.")
    return render(request, 'update_post.html', {'post': post})


# DELETAR POST
@login_required
def delete_post(request, post_id):
    if request.method == 'POST':
        try:
            response = requests.delete(f'{API_URL_POSTS}?id={post_id}', timeout=5)
            response.raise_for_status()
            messages.success(request, "Post deletado com sucesso!")
        except requests.exceptions.RequestException:
            messages.error(request, "Erro ao deletar post.")
        return redirect('dashboard')

    # GET: mostrar confirmação
    post = None
    try:
        resp = requests.get(f'{API_URL_POSTS}/show/{post_id}', timeout=5)
        if resp.ok:
            data_json = resp.json()
            post = data_json.get('post')
    except requests.exceptions.RequestException:
        pass
    return render(request, 'confirm_delete_post.html', { 'post': post, 'post_id': post_id })

@login_required
def delete_comment(request, post_id, comment_id):
    try:
        resp = requests.delete(f'{API_URL_COMMENTS}?id={comment_id}', timeout=5)
        resp.raise_for_status()
        messages.success(request, "Comentário removido!")
    except requests.exceptions.RequestException:
        messages.error(request, "Erro ao remover comentário.")
    return redirect('post_detail', post_id=post_id)

# Helpers
def get_or_create_backend_user_id(dj_user: User):
    email = dj_user.email
    name = dj_user.username
    try:
        resp = requests.get(API_URL_USERS, timeout=5)
        resp.raise_for_status()
        for u in resp.json().get('users', []):
            if u.get('email') == email:
                return u.get('id')
    except requests.exceptions.RequestException:
        pass
    try:
        data = { 'name': name, 'email': email, 'password': 'AutoGen123!' }
        resp = requests.post(API_URL_USERS, data=data, timeout=5)
        if resp.ok:
            created = resp.json().get('user', {})
            return created.get('id')
    except requests.exceptions.RequestException:
        pass
    return None
