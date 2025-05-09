import type { MenuItem } from "@/layouts/default-layout/config/types";

const MainMenuConfig: Array<MenuItem> = [
    {
        pages: [
            {
                heading: "Dashboard",
                name: "dashboard",
                route: "/dashboard",
                keenthemesIcon: "element-11",
            },
            {
                heading: "Beranda",
                route: "/dashboard_pengguna",
                name: "d_pengguna",
                keenthemesIcon: "element-10",
            },
        ],
    },

    // WEBSITE
    {
        heading: "Website",
        route: "/dashboard/website",
        name: "website",
        pages: [
            // MASTER
            {
                sectionTitle: "Master",
                route: "/master",
                keenthemesIcon: "cube-3",
                name: "master",
                sub: [
                    {
                        sectionTitle: "User",
                        route: "/users",
                        name: "master-user",
                        sub: [
                            {
                                heading: "Role",
                                name: "master-role",
                                route: "/dashboard/master/users/roles",
                            },
                            {
                                heading: "User",
                                name: "master-user",
                                route: "/dashboard/master/users",
                            },
                        ],
                    },
                ],
            },
            {
                heading: "Setting",
                route: "/dashboard/setting",
                name: "setting",
                keenthemesIcon: "setting-2",
            },
            // {
            //     heading: "Dashboard Pengguna",
            //     route: "/dashboard pengguna",
            //     name: "d_pengguna",
            //     keenthemesIcon: "bi bi-cart",
            // },
            {
                heading: "Akun",
                route: "/dashboard/akun",
                name: "akun",
                keenthemesIcon: "bi bi-person",
            },
            {
                heading: "Kurir",
                route: "/dashboard/kurir",
                name: "kurir",
                keenthemesIcon: "bi bi-person-fill",
            },
            {
                heading: "Pesanan",
                route: "/dashboard/pesanan",
                name: "pesanan",
                keenthemesIcon: "bi bi-cart",
            },
            {
                heading: "Pelanggan",
                route: "/dashboard/pelanggan",
                name: "pelanggan",
                keenthemesIcon: "bi bi-person",
            },
            {
                sectionTitle: "Order",
                route: "/dashboard/order",
                keenthemesIcon: "bi bi-cart",
                name: "order",
                sub: [
                    {
                        heading: "Data Order",
                        route: "/dashboard/order/data",
                        name: "data",
                        keenthemesIcon: "bi bi-cart",
                    },
                    {
                        heading: "Ordered",
                        route: "/dashboard/order/ordered",
                        name: "ordered",
                        keenthemesIcon: "bi bi-cart",
                    },
                    {
                        heading: "Input Order",
                        route: "/dashboard/order/input",
                        name: "input",
                        keenthemesIcon: "bi bi-cart",
                    },
                    {
                        heading: "Riwayat",
                        route: "/dashboard/order/riwayat",
                        name: "riwayat",
                        keenthemesIcon: "bi bi-cart",
                    },
                    {
                        heading: "Riwayatt",
                        route: "/dashboard/order/riwayatt",
                        name: "riwayatt",
                        keenthemesIcon: "bi bi-cart",
                    },
                ],
            },
            // {
            //     heading: "Order",
            //     route: "/dashboard/order",
            //     name: "order",
            //     keenthemesIcon: "bi bi-cart",
            // },
            {
                heading: "Pengiriman",
                route: "/dashboard/pengiriman",
                name: "pengiriman",
                keenthemesIcon: "bi bi-truck",
            },
            {
                heading: "Transaksi",
                route: "/dashboard/transaksi",
                name: "transaksi",
                keenthemesIcon: "bi bi-currency-exchange",
            },
        ],
    },
];

export default MainMenuConfig;
