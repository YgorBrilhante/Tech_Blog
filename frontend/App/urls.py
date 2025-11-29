from django.urls import path
from . import views

urlpatterns = [
    path('', views.index, name='index'),
    path('dashboard/', views.dashboard, name='dashboard'),

    path("login/", views.login_view, name="login"),
    path("logout/", views.logout_view, name="logout"),
    path("register/", views.register_view, name="register"),
    path("profile_delete/", views.profile_delete, name="profile_delete"),
    path("profile_edit/", views.profile_edit, name="profile_edit"),
    path("profile/", views.profile_view, name="profile"),
    path("create_post/", views.create_post, name="create_post"),


    # Rotas da API REST
   
    #Users
    path('users/', views.users_list, name='users_list'),
    path('users/new/', views.user_create, name='user_create'),
    path('users/<int:id>/', views.user_detail, name='user_detail'),
    path('users/<int:id>/edit/', views.user_update, name='user_update'),
    path('users/<int:id>/delete/', views.user_delete, name='user_delete'),
    
    #Posts
    path('post/<str:post_id>/', views.post_detail, name='post_detail'),
    path('post/<str:post_id>/update/', views.update_post, name='update_post'),
    path('post/<str:post_id>/delete/', views.delete_post, name='delete_post'),
    path('post/<str:post_id>/comment/<str:comment_id>/delete/', views.delete_comment, name='delete_comment'),
]
