// Texto de interface (chrome) centralizado: nenhuma view deve ter string
// literal voltada ao usuário — só ler daqui. Ver docs/convencoes-arquitetura.md.
//
// Dados de mock/placeholder (arrays de posts/eventos/aniversariantes ainda
// sem endpoint real, ex.: em DashboardView.vue) ficam FORA daqui — não são
// texto de interface, são dado de exemplo que será substituído por dado
// real da API.
export const strings = {
  common: {
    brand: 'Copiloto de Mídia',
  },

  themeToggle: {
    enableLight: 'Ativar tema claro',
    enableDark: 'Ativar tema escuro',
  },

  home: {
    nav: {
      features: 'Recursos',
      howItWorks: 'Como funciona',
      login: 'Entrar',
      register: 'Criar conta',
    },
    badge: {
      checking: 'Verificando...',
      online: 'API online',
      offline: 'API indisponível',
      active: 'Copiloto ativo agora',
    },
    hero: {
      title: 'Sua mídia social no piloto automático.',
      subtitle:
        'Stories, mensagens de WhatsApp e o cronograma de publicações do seu time — organizados e sugeridos por IA, em um só lugar.',
      ctaPrimary: 'Começar agora',
      ctaSecondary: 'Ver como funciona',
      note: 'USADO PELOS TIMES DE MARKETING E CONTEÚDO',
    },
    preview: {
      contact: 'WhatsApp · Equipe Sul',
      suggestionSent: 'Sugestão de resposta enviada ✓',
      storiesLabel: 'STORIES AGENDADOS',
      eventTime: 'Hoje, 14:00 · Instagram',
      eventLabel: 'Cronograma sugerido pela IA',
    },
    features: {
      sectionTitle: 'Tudo que seu time precisa',
      sectionSubtitle:
        'De stories a mensagens diretas, o copiloto cuida da rotina para o time focar no conteúdo.',
      items: [
        {
          icon: 'circle',
          title: 'Stories automáticos',
          text: 'Cria e publica stories a partir das suas fotos e vídeos, no melhor horário.',
        },
        {
          icon: 'message',
          title: 'WhatsApp integrado',
          text: 'Sugere e envia respostas e avisos direto pelo WhatsApp da equipe.',
        },
        {
          icon: 'grid',
          title: 'Cronograma inteligente',
          text: 'Organiza os avisos e publicações da semana e ajusta sozinho os horários.',
        },
        {
          icon: 'chart',
          title: 'Métricas em tempo real',
          text: 'Acompanha alcance e engajamento sem sair do painel.',
        },
      ],
    },
    steps: {
      sectionTitle: 'Como funciona',
      sectionSubtitle: 'Três passos para o time todo publicar sem esforço.',
      items: [
        {
          number: 1,
          title: 'Conecte suas contas',
          text: 'Instagram, WhatsApp e o calendário do time em poucos cliques.',
        },
        {
          number: 2,
          title: 'Envie fotos e vídeos',
          text: 'O copiloto sugere legendas, formatos de story e os melhores horários.',
        },
        {
          number: 3,
          title: 'Publique com aprovação',
          text: 'O time revisa em um clique e tudo sai no horário certo.',
        },
      ],
    },
    footer: {
      copyright: '© 2026 Copiloto de Mídia',
    },
  },

  auth: {
    login: {
      title: 'Entrar',
      emailLabel: 'E-mail',
      passwordLabel: 'Senha',
      submit: 'Entrar',
      submitLoading: 'Entrando...',
      genericError: 'Não foi possível fazer login.',
      noAccount: 'Não tem conta?',
      registerLink: 'Cadastre-se',
    },
    register: {
      title: 'Criar conta',
      nameLabel: 'Nome',
      emailLabel: 'E-mail',
      passwordLabel: 'Senha',
      passwordConfirmationLabel: 'Confirmar senha',
      submit: 'Criar conta',
      submitLoading: 'Criando conta...',
      genericError: 'Não foi possível criar a conta.',
      hasAccount: 'Já tem conta?',
      loginLink: 'Entrar',
    },
  },

  dashboard: {
    logout: 'Sair',
    loading: 'Carregando...',
    logoSub: 'Ministério de Mídia',
    collapseSidebar: 'Recolher menu',
    expandSidebar: 'Expandir menu',
    nav: {
      dashboard: 'Dashboard',
      birthdays: 'Aniversariantes',
      captions: 'Legendas',
      settings: 'Configurações',
      comingSoon: 'Em breve',
    },
    roles: {
      owner: 'Administrador',
      pro: 'Plano Pro',
      free: 'Plano Free',
      fallback: 'Usuário',
    },
    greeting: {
      morning: 'Bom dia',
      morningEmoji: '☀️',
      afternoon: 'Boa tarde',
      afternoonEmoji: '🙏',
      evening: 'Boa noite',
      eveningEmoji: '🌙',
      subtitlePrefix: 'Você tem',
      subtitleScheduled: (count) => `${count} publicações agendadas`,
      subtitleMiddle: 'para hoje e',
      subtitleBirthdays: (count) => `${count} aniversariante`,
      subtitleSuffix: '.',
    },
    stats: {
      postsThisMonth: 'Posts este mês',
      scheduled: 'Agendados',
      birthdaysThisMonth: 'Aniversariantes no mês',
    },
    birthdayBanner: {
      titleSuffix: 'faz aniversário hoje!',
      cta: '🎉 Ver Arte',
    },
    agenda: {
      sectionTitle: 'Agenda de Hoje',
      tags: {
        auto: 'Automático',
        wait: 'Aguardando',
        manual: 'Manual',
      },
    },
  },

  aniversariantes: {
    pageTitle: 'Aniversariantes',
    loading: 'Carregando membros...',
    loadError: 'Não foi possível carregar os membros.',
    subtitle: (count) => `${count} membro${count === 1 ? '' : 's'} cadastrado${count === 1 ? '' : 's'}`,
    cadastrarBtn: 'Cadastrar Membro',
    cadastrarPrimeiroBtn: 'Cadastrar primeiro membro',
    searchPlaceholder: 'Buscar membro...',
    filtros: {
      todos: 'Todos',
      hoje: '🎉 Hoje',
      mes: 'Este mês',
      inativos: 'Inativos',
    },
    resultsCount: (count) => `${count} membro${count === 1 ? '' : 's'}`,
    emptyState: {
      title: 'Nenhum membro encontrado',
      text: 'Cadastre os membros da igreja para gerenciar os aniversários.',
    },
    card: {
      inativoBadge: 'Inativo',
      hojeBadge: '🎉 Hoje!',
      emDias: (dias) => `em ${dias} dia${dias === 1 ? '' : 's'}`,
      anos: (idade) => `${idade} anos`,
      editar: 'Editar',
      desativar: 'Desativar',
    },
    modalForm: {
      tituloCriar: 'Cadastrar Membro',
      tituloEditar: 'Editar Membro',
      fotoLabel: 'Foto do membro',
      fotoHint: 'JPG, PNG ou WEBP • Clique para selecionar',
      nomeLabel: 'Nome completo *',
      nomePlaceholder: 'Ex: Maria Santos',
      dataLabel: 'Data de nascimento *',
      whatsappLabel: 'WhatsApp',
      whatsappPlaceholder: '+55 62 99999-0000',
      ignorarAnoLabel: 'Não sei o ano / exibir só dia e mês',
      ignorarAnoHint: 'A idade não será calculada nem exibida',
      ativoLabel: 'Membro ativo',
      ativoHint: 'Inativos não aparecem nas listagens principais',
      obsLabel: 'Observações',
      obsPlaceholder: 'Notas internas da equipe...',
      cancelar: 'Cancelar',
      salvar: 'Salvar Membro',
    },
    modalConfirm: {
      titulo: 'Desativar membro?',
      texto: 'O membro será desativado e não aparecerá mais nas listagens. O histórico é preservado.',
      cancelar: 'Cancelar',
      confirmar: 'Desativar',
    },
    toasts: {
      nomeObrigatorio: 'Digite o nome do membro',
      dataObrigatoria: 'Selecione a data de nascimento',
      atualizado: 'Membro atualizado com sucesso',
      cadastrado: 'Membro cadastrado com sucesso',
      desativado: 'Membro desativado',
      erroSalvar: 'Não foi possível salvar o membro. Tente novamente.',
      erroDesativar: 'Não foi possível desativar o membro. Tente novamente.',
    },
  },
}
