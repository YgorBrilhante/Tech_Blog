CREATE TABLE public.users (
	id uuid DEFAULT gen_random_uuid() NOT NULL,
	"name" varchar(100) NOT NULL,
	email varchar(100) NOT NULL,
	"password" varchar(255) NOT NULL,
	created_at timestamp DEFAULT CURRENT_TIMESTAMP NULL,
	CONSTRAINT users_email_key UNIQUE (email),
	CONSTRAINT users_pkey PRIMARY KEY (id)
);



CREATE TABLE public.posts (
	id uuid DEFAULT gen_random_uuid() NOT NULL,
	titulo varchar(255) NOT NULL,
	conteudo text NOT NULL,
	user_id uuid NOT NULL,
	created_at timestamp DEFAULT CURRENT_TIMESTAMP NULL,
	CONSTRAINT posts_pkey PRIMARY KEY (id)
);

ALTER TABLE public.posts ADD CONSTRAINT posts_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;



CREATE TABLE public.tags (
	id uuid DEFAULT gen_random_uuid() NOT NULL,
	"name" varchar(100) NOT NULL,
	created_at timestamp DEFAULT CURRENT_TIMESTAMP NULL,
	CONSTRAINT tags_pkey PRIMARY KEY (id)
);



CREATE TABLE public.post_tags (
	post_id uuid NOT NULL,
	tag_id uuid NOT NULL,
	CONSTRAINT post_tags_pkey PRIMARY KEY (post_id, tag_id)
);

ALTER TABLE public.post_tags ADD CONSTRAINT post_tags_post_id_fkey FOREIGN KEY (post_id) REFERENCES public.posts(id) ON DELETE CASCADE;
ALTER TABLE public.post_tags ADD CONSTRAINT post_tags_tag_id_fkey FOREIGN KEY (tag_id) REFERENCES public.tags(id) ON DELETE CASCADE;



CREATE TABLE public."comments" (
	id uuid DEFAULT gen_random_uuid() NOT NULL,
	post_id uuid NOT NULL,
	user_id uuid NOT NULL,
	"content" text NOT NULL,
	created_at timestamp DEFAULT CURRENT_TIMESTAMP NULL,
	CONSTRAINT comments_pkey PRIMARY KEY (id)
);

ALTER TABLE public."comments" ADD CONSTRAINT comments_post_id_fkey FOREIGN KEY (post_id) REFERENCES public.posts(id) ON DELETE CASCADE;
ALTER TABLE public."comments" ADD CONSTRAINT comments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;

-- Melhora a busca de posts por autor e joins
CREATE INDEX idx_posts_user_id ON public.posts (user_id);

-- Acelera a listagem da home (ordenação por data)
CREATE INDEX idx_posts_created_at ON public.posts (created_at DESC);

-- Melhora a busca de posts por título
CREATE INDEX idx_posts_titulo ON public.posts (titulo);

-- Acelera o carregamento de comentários de um post específico
-- Índice composto: filtra pelo post e já entrega ordenado por data
CREATE INDEX idx_comments_post_created ON public."comments" (post_id, created_at);

-- Melhora joins para mostrar "comentários deste usuário"
CREATE INDEX idx_comments_user_id ON public."comments" (user_id);

-- Acelera a busca de posts por tag (o lado reverso da relação N:N)
CREATE INDEX idx_post_tags_tag_id ON public.post_tags (tag_id);

-- Busca rápida de tags pelo nome
CREATE INDEX idx_tags_name ON public.tags ("name");

