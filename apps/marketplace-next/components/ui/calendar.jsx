import * as React from "react";
import { DayPicker } from "react-day-picker";
import { ChevronLeft, ChevronRight } from "lucide-react";

import { cn } from "@/lib/utils";
import { buttonVariants } from "@/components/ui/button";

function Calendar({ className, classNames, showOutsideDays = true, ...props }) {
  return (
    <DayPicker
      showOutsideDays={showOutsideDays}
      className={cn("p-3", className)}
      classNames={{
        months: "flex flex-col gap-4",
        month: "space-y-4",
        caption: "flex justify-center pt-1 relative items-center",
        caption_label: "text-sm font-semibold text-gray-900",
        nav: "space-x-1 flex items-center",
        nav_button: cn(
          buttonVariants({ variant: "ghost", rounded: "xl" }),
          "h-8 w-8 p-0 text-gray-700 hover:bg-gray-100"
        ),
        nav_button_previous: "absolute left-1",
        nav_button_next: "absolute right-1",
        table: "w-full border-collapse space-y-1",
        head_row: "flex",
        head_cell: "w-9 text-[11px] font-semibold text-gray-500",
        row: "flex w-full mt-2",
        cell: "relative w-9 h-9 p-0 text-center text-sm",
        day: cn(
          buttonVariants({ variant: "ghost", rounded: "xl" }),
          "h-9 w-9 p-0 font-semibold text-gray-900 hover:bg-gray-100"
        ),
        day_selected:
          "bg-blue-600 text-white hover:bg-blue-600 hover:text-white focus:bg-blue-600 focus:text-white",
        day_today: "bg-blue-50 text-blue-700",
        day_outside: "text-gray-400 opacity-60",
        day_disabled: "text-gray-300 opacity-50",
        ...classNames,
      }}
      components={{
        IconLeft: (iconProps) => <ChevronLeft className="size-4" {...iconProps} />,
        IconRight: (iconProps) => <ChevronRight className="size-4" {...iconProps} />,
      }}
      {...props}
    />
  );
}

export { Calendar };

