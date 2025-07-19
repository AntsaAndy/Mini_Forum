<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{% block title %}Mon Forum Symfony{% endblock %}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {# Liens CSS (Bootstrap, Tailwind, ou ton propre CSS) #}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    {% block stylesheets %}{% endblock %}
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ path('forum_index') }}">Mon Forum</a>

            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    {% if app.user %}
                        <li class="nav-item">
                            <a class="nav-link" href="#">{{ app.user.username }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ path('app_logout') }}">Déconnexion</a>
                        </li>
                    {% else %}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ path('app_login') }}">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ path('app_register') }}">Inscription</a>
                        </li>
                    {% endif %}
                </ul>
            </div>
        </div>
    </nav>

    <main class="container">
        {% for label, messages in app.flashes %}
            {% for message in messages %}
                <div class="alert alert-{{ label }}">
                    {{ message }}
                </div>
            {% endfor %}
        {% endfor %}

        {% block body %}{% endblock %}
    </main>

    {# Scripts JS #}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    {% block javascripts %}{% endblock %}
</body>
</html>
